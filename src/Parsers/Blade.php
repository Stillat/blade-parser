<?php

namespace Stillat\BladeParser\Parsers;

use Stillat\BladeParser\Documents\Template;
use Stillat\BladeParser\Nodes\CommentNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\EchoNode;
use Stillat\BladeParser\Nodes\Node;
use Stillat\BladeParser\Nodes\PhpNode;
use Stillat\BladeParser\Nodes\StaticNode;
use Stillat\BladeParser\Nodes\Structures\ExtendsNode;
use Stillat\BladeParser\Nodes\TagPairNode;
use Stillat\BladeParser\Nodes\VerbatimNode;
use Stillat\BladeParser\Parsers\Concerns\DetectsEchos;
use Stillat\BladeParser\Parsers\Concerns\DetectsUnSafeEchos;
use Stillat\BladeParser\Parsers\Concerns\HandlesCustomDirectives;
use Stillat\BladeParser\Parsers\Concerns\HandlesInputReplacements;
use Stillat\BladeParser\Parsers\Concerns\ManagesNewLines;
use Stillat\BladeParser\Parsers\Concerns\PeeksAtInputTokens;
use Stillat\BladeParser\Parsers\Concerns\ResetsStringLiterals;
use Stillat\BladeParser\Parsers\Concerns\ScansForComments;
use Stillat\BladeParser\Parsers\Concerns\ScansForComponents;
use Stillat\BladeParser\Parsers\Concerns\ScansForDirectives;
use Stillat\BladeParser\Parsers\Concerns\ScansForSafeEchos;
use Stillat\BladeParser\Parsers\Concerns\ScansForStrings;
use Stillat\BladeParser\Parsers\Concerns\ScansForUnSafeEchos;
use Stillat\BladeParser\Parsers\Directives\LanguageDirective;
use Stillat\BladeParser\Parsers\Structures\PhpBlockParser;
use Stillat\BladeParser\Parsers\Structures\VerbatimBlockParser;

class Blade
{
    use HandlesInputReplacements,
        HandlesCustomDirectives,
        ManagesNewLines,
        ResetsStringLiterals,
        PeeksAtInputTokens,
        DetectsEchos, DetectsUnSafeEchos,
        ScansForComments,
        ScansForComponents,
        ScansForStrings,
        ScansForUnSafeEchos,
        ScansForDirectives,
        ScansForSafeEchos;

    const TOKEN_BLADE_START = '@';
    const TOKEN_ESCAPE_BLADE = '@';
    const TOKEN_BLADE_DIRECTIVE_INPUT_START = '(';
    const TOKEN_BLADE_DIRECTIVE_END = ')';
    const TOKEN_STRING_ESCAPE = '\\';
    const TOKEN_LINE_SEPARATOR = "\n";

    const TOKEN_COMMENT_DELIMITER = '-';
    const TOKEN_ECHO_START = '{';
    const TOKEN_ECHO_END = '}';
    const TOKEN_ECHO_UNSAFE = '!';

    const TOKEN_COMPONENT_START = '<';
    const TOKEN_COMPONENT_SELF_CLOSE_START = '/';
    const TOKEN_COMPONENT_SELF_CLOSE_END = '>';

    const COMMENT_START = '{{--';
    const COMMENT_END = '--}}';

    private $stringInitiators = ['\'', '"'];

    private $previous = null;
    private $current = null;
    private $currentLine = 1;
    private $next = null;
    private $tokens = [];

    /**
     * A reference of the original template data.
     *
     * Some pre-process steps may adjust the $tokens list.
     *
     * @var array
     */
    private $referenceTokens = [];
    private $currentIndex = 0;
    private $tokenLength = 0;
    private $directives = [];
    private $isParsingString = false;
    private $stringStartOn = -1;
    private $currentStringInitiator = null;

    private $nodeReferences = [];
    private $newLineType = "\n";

    private $languageDirectives = [];
    private $currentSegment = '';

    /**
     * The VerbatimBlockParser instance.
     *
     * @var VerbatimBlockParser
     */
    private $verbatimParser = null;

    /**
     * Then PhpBlockParser instance.
     *
     * @var PhpBlockParser
     */
    private $phpBlockParser = null;

    public function __construct()
    {
        $this->verbatimParser = new VerbatimBlockParser();
        $this->phpBlockParser = new PhpBlockParser();
        $this->registerDefaultDirectives();
    }

    protected function registerDefaultDirectives()
    {
        foreach (glob(__DIR__.'/Directives/*.php') as $file) {
            $fileBaseName = basename($file);
            $className = substr($fileBaseName, 0, strlen($fileBaseName) - 4);

            $abstracts = [
                LanguageDirective::class,
            ];

            $fqn = 'Stillat\\BladeParser\\Parsers\\Directives\\'.$className;

            if (class_exists($fqn) && in_array($fqn, $abstracts) === false) {
                $this->registerDirective(new $fqn);
            }
        }
    }

    public function registerDirective(LanguageDirective $directive)
    {
        $this->languageDirectives[$directive->name] = $directive;
    }

    public function parse($input)
    {
        // Convert custom PHP blocks into @php and @endphp directives first.
        $input = str_replace('<?php', '@php', $input);
        $input = str_replace('?>', '@endphp', $input);

        $input = $this->doReplacements($input);

        $this->reset();

        $this->newLineType = $this->detectNewLine($input);

        if ($this->newLineType === '') {
            $this->newLineType = self::TOKEN_LINE_SEPARATOR;
        }

        if ($this->newLineType === trim($input)) {
            $this->newLineType = self::TOKEN_LINE_SEPARATOR;
        }

        $this->tokens = mb_str_split($this->normalizeLineEndings($input));
        $this->referenceTokens = $this->tokens;
        $this->tokenLength = count($this->tokens);

        // Some pre-processing steps.
        $this->doVerbatimPreProcessing()
            ->doPhpBlockPreProcessing();

        $currentDirective = '';
        $this->directives = [];
        $this->currentSegment = '';

        $lastDirectiveType = '';

        for ($i = 0; $i < $this->tokenLength; $i++) {
            $this->currentIndex = $i;
            $this->current = $this->tokens[$i];
            $nextIndex = $i + 1;

            if ($nextIndex < $this->tokenLength) {
                $this->next = $this->tokens[$nextIndex];
            }

            if ($this->current === self::TOKEN_ECHO_START && (
                    $this->next !== null && $this->next === self::TOKEN_ECHO_START
                ) && $this->isStartOfComment()) {
                $scanResults = $this->scanToEndOfComment($i);

                $this->directives[] = [
                    'content' => $scanResults[0],
                    'start' => $i,
                    'end' => $scanResults[1],
                    'line' => $this->currentLine,
                    'type' => 'comment',
                ];

                $previousIndex = $scanResults[1];
                if ($previousIndex < $this->tokenLength) {
                    $this->previous = $this->tokens[$previousIndex];
                }

                $i = $scanResults[1];

                $lastDirectiveType = 'comment';

                continue;
            } elseif ($this->previous !== null && $this->previous === self::TOKEN_ESCAPE_BLADE && $this->couldBeEscapedEcho()) {
                $this->currentSegment = '';

                $echoResults = $this->scanToEndOfEcho($i);

                $this->directives[] = [
                    'content' => ltrim($echoResults[0], self::TOKEN_ESCAPE_BLADE),
                    'type' => 'literal',
                ];

                $previousIndex = $echoResults[1];
                if ($previousIndex < $this->tokenLength) {
                    $this->previous = $this->tokens[$previousIndex];
                }

                $i = $echoResults[1];
                $lastDirectiveType = 'literal';

                continue;
            } elseif ($this->current === self::TOKEN_ECHO_START && $this->isStartingUnsafeEcho()) {
                $this->convertSegmentToStringLiteral();

                $echoResults = $this->scanToEndOfUnsafeEcho($i);

                $this->directives[] = [
                    'content' => $echoResults[0],
                    'start' => $i,
                    'end' => $echoResults[1],
                    'line' => $this->currentLine,
                    'open_count' => $echoResults[2],
                    'type' => 'unsafe_echo',
                ];

                $previousIndex = $echoResults[1];
                if ($previousIndex < $this->tokenLength) {
                    $this->previous = $this->tokens[$previousIndex];
                }

                $i = $echoResults[1];

                $lastDirectiveType = 'unsafe_echo';

                continue;
            } elseif ($this->current === self::TOKEN_ECHO_START && $this->isStartingBladeEcho()) {
                $this->convertSegmentToStringLiteral();

                $echoResults = $this->scanToEndOfEcho($i);

                $this->directives[] = [
                    'content' => $echoResults[0],
                    'start' => $i,
                    'end' => $echoResults[1],
                    'line' => $this->currentLine,
                    'open_count' => $echoResults[2],
                    'type' => 'echo',
                ];

                $previousIndex = $echoResults[1];
                if ($previousIndex < $this->tokenLength) {
                    $this->previous = $this->tokens[$previousIndex];
                }

                $nextIterationIndex = $echoResults[1] + 1;

                if ($nextIterationIndex < $this->tokenLength) {
                    if ($this->tokens[$nextIterationIndex] == self::TOKEN_LINE_SEPARATOR) {
                        $this->directives[] = [
                            'type' => 'newline',
                            'content' => "\n",
                        ];
                        $this->currentSegment = '';
                    }
                }

                $i = $echoResults[1];
                $lastDirectiveType = 'echo';
                $this->fastForward($i);

                continue;
            } elseif ($this->current === self::TOKEN_COMPONENT_START && $this->isStartingBladeComponent()) {
                $this->convertSegmentToStringLiteral();

                $componentResults = $this->scanToEndOfComponent($i);

                $this->directives[] = [
                    'content' => $componentResults[0],
                    'start' => $i,
                    'end' => $componentResults[1],
                    'line' => $this->currentLine,
                    'type' => 'component',
                ];

                $previousIndex = $componentResults[1];
                if ($previousIndex < $this->tokenLength) {
                    $this->previous = $this->tokens[$previousIndex];
                }
                $lastDirectiveType = 'component';
                $i = $componentResults[1];

                continue;
            } elseif ($this->current === self::TOKEN_BLADE_START && $this->isStartingBladeEscapeSequence() === false && ($this->previous == "\n" || $this->previous == null || ctype_space($this->previous) || ctype_punct($this->previous) || $this->previous == '?')) {
                $this->convertSegmentToStringLiteral();

                $scanResults = $this->scanToEndOfDirective($i);

                // Did we just get ourselves into a verbatim block?
                if ($scanResults[1] == VerbatimBlockParser::VERBATIM && $this->verbatimParser->isStartOfTagPair($i)) {
                    $verbatimDetails = $this->verbatimParser->getExtraction($i);

                    $this->directives[] = array_merge($verbatimDetails, [
                        'name' => VerbatimBlockParser::VERBATIM,
                        'type' => VerbatimBlockParser::VERBATIM,
                    ]);

                    $lastDirectiveType = VerbatimBlockParser::VERBATIM;

                    // The -12 is so that we do not chop off the "@endverbatim".
                    $skipIndex = $verbatimDetails['end'] - 12;
                    $this->fastForward($skipIndex);
                    $i = $skipIndex;
                    continue;
                } elseif (($this->isReplacedPhpExtraction($scanResults[1]) || $scanResults[1] == PhpBlockParser::NAME_PHP)
                    && ($this->next != null && $this->next != PhpBlockParser::PHP_DIRECTIVE_INPUT_START)
                    && $this->phpBlockParser->isValidPhpBlockStartLocation($i)) {
                    $phpBlockDetails = $this->phpBlockParser->getExtraction($i);

                    $this->directives[] = array_merge($phpBlockDetails, [
                        'name' => PhpBlockParser::NAME_PHP,
                        'type' => PhpBlockParser::NAME_PHP,
                    ]);

                    $lastDirectiveType = PhpBlockParser::NAME_PHP;

                    $skipIndex = $phpBlockDetails['end'];
                    $this->fastForward($skipIndex);
                    $i = $skipIndex;
                    $this->currentSegment = '';
                    continue;
                }

                $potentialEndVerbatim = $this->verbatimParser->parseEndTagComponents($scanResults[0]);

                if ($potentialEndVerbatim != null) {
                    if ($this->verbatimParser->isValidEndPair($i)) {
                        $this->directives[] = [
                            'content' => $scanResults[0],
                            'inner_content' => $scanResults[3],
                            'start' => $i,
                            'end' => $scanResults[2],
                            'line' => $this->currentLine,
                            'name' => VerbatimBlockParser::ENDVERBATIM,
                            'type' => 'directive',
                        ];

                        $skipIndex = $i + 11; // $potentialEndVerbatim['length'];

                        $this->currentSegment = '';
                        $this->fastForward($skipIndex);
                        $i = $skipIndex;
                    } else {
                        $this->directives[] = [
                            'type' => 'literal',
                            'content' => $potentialEndVerbatim['directive'].$potentialEndVerbatim['literal'],
                        ];

                        $skipIndex = $i + $potentialEndVerbatim['length'];
                        $this->currentSegment = '';
                        $this->fastForward($skipIndex);
                        $i = $skipIndex;
                    }

                    continue;
                }

                $this->directives[] = [
                    'content' => $scanResults[0],
                    'inner_content' => $scanResults[3],
                    'start' => $i,
                    'end' => $scanResults[2],
                    'line' => $this->currentLine,
                    'name' => $scanResults[1],
                    'type' => 'directive',
                ];

                // Did the scanner break on new line?
                if ($scanResults[4] === true) {
                    $this->directives[] = [
                        'type' => 'newline',
                        'content' => "\n",
                    ];
                }

                $previousIndex = $scanResults[2];
                if ($previousIndex < $this->tokenLength) {
                    $this->previous = $this->tokens[$previousIndex];
                }

                $lastDirectiveType = 'directive';
                $i = $scanResults[2];

                continue;
            } elseif ($this->current === self::TOKEN_BLADE_START && $this->isStartingBladeEscapeSequence()) {
                $currentDirective = '';
            }

            $this->currentSegment .= $this->current;

            if ($i === $this->tokenLength - 1) {
                $this->convertSegmentToStringLiteral();
            }

            $this->previous = $this->current;
            $lastDirectiveType = '';
        }

        $this->directives = $this->rearrangeDirectives($this->directives);

        $nodeTree = $this->processDirectives($this->directives);

        return new Template($nodeTree, $this->nodeReferences, $this->newLineType);
    }

    private function reset()
    {
        $this->verbatimParser->reset();
        $this->phpBlockParser->reset();
        $this->directives = [];
        $this->previous = null;
        $this->current = null;
        $this->currentLine = 1;
        $this->next = null;
        $this->tokens = [];
        $this->referenceTokens = [];
        $this->currentIndex = 0;
        $this->tokenLength = 0;
        $this->isParsingString = false;
        $this->stringStartOn = -1;
        $this->currentStringInitiator = null;
        $this->nodeReferences = [];
        $this->newLineType = "\n";
    }

    private function doPhpBlockPreProcessing()
    {
        $this->phpBlockParser->setTokens($this->tokens);
        $this->phpBlockParser->parse();

        $phpOffsets = $this->phpBlockParser->getPairOffsets();

        foreach ($phpOffsets as $offset) {
            $len = $offset['content_end'] - $offset['content_start'];
            $replace = str_repeat('-', $len);

            array_splice($this->tokens, $offset['content_start'], $len, mb_str_split($replace));
        }

        return $this;
    }

    private function doVerbatimPreProcessing()
    {
        $this->verbatimParser->setTokens($this->tokens);
        $this->verbatimParser->parse();

        $verbatimOffsets = $this->verbatimParser->getPairOffsets();

        foreach ($verbatimOffsets as $offset) {
            $len = $offset['content_end'] - $offset['content_start'];
            $replace = str_repeat('?', $len);

            array_splice($this->tokens, $offset['content_start'], $len, mb_str_split($replace));
        }

        return $this;
    }

    private function fastForward($index)
    {
        if ($index >= $this->tokenLength) {
            $this->previous = $this->tokens[$this->tokenLength - 1];
            $this->next = null;

            return;
        }
        $this->current = $this->tokens[$index];
        $nextIndex = $index + 1;

        if ($nextIndex < $this->tokenLength) {
            $this->next = $this->tokens[$nextIndex];
        } else {
            $this->next = null;
        }

        $this->previous = $this->tokens[$index - 1];
    }

    private function isStartingBladeComponent()
    {
        if (($this->currentIndex + 3) < $this->tokenLength) {
            $peek = implode(array_slice($this->tokens, $this->currentIndex, 3));

            if ($peek === '<x-') {
                return true;
            }
        }

        return false;
    }

    private function isStartingBladeEscapeSequence()
    {
        if ($this->next === null) {
            return false;
        }

        if ($this->previous !== null) {
            if ($this->current === self::TOKEN_BLADE_START && $this->previous === self::TOKEN_ESCAPE_BLADE) {
                return true;
            }
        }

        if ($this->current === self::TOKEN_ESCAPE_BLADE && $this->next === self::TOKEN_BLADE_START) {
            return true;
        }

        if ($this->current === self::TOKEN_ESCAPE_BLADE && $this->next === self::TOKEN_ECHO_START) {
            return true;
        }

        return false;
    }

    private function rearrangeDirectives($directives)
    {
        if (count($directives) <= 1) {
            return $directives;
        }

        if (($directives[0]['type'] === 'directive' && $directives[0]['name'] === 'extends') && $directives[1]['type'] === 'literal') {
            $adjusted = [];
            $adjusted[] = $directives[1];
            $adjusted[] = $directives[0];

            $adjusted[0]['content'] = $this->invertNewLine($adjusted[0]['content']);

            if (count($directives) < 3) {
                return $adjusted;
            }

            $theRest = array_slice($directives, 2);

            return array_merge($adjusted, $theRest);
        }

        return $directives;
    }

    private function invertNewLine($content)
    {
        if (mb_strlen($content) < 2) {
            return $content;
        }

        $firstChar = mb_substr($content, 0, 1);
        if ($firstChar === self::TOKEN_LINE_SEPARATOR) {
            $string = mb_substr($content, 1);

            return $string.$firstChar;
        }

        return $content;
    }

    private function processDirectives($directives)
    {
        // TODO: Refactor this to some "DirectiveProcessor" class system
        //       Since this isn't "really" related to the parser.
        $nodes = [];

        // TODO: This stack method will NOT work with nested types.
        //       This is just something to get the base level working.
        $stack = new DirectiveStack();

        for ($i = 0; $i < count($this->directives); $i++) {
            $current = $this->directives[$i];

            if ($current instanceof Node) {
                continue;
            } elseif (is_array($current)) {
                $type = $current['type'];

                if ($type === PhpBlockParser::NAME_PHP) {
                    $nodes[] = $this->makePhpNode($current);
                } elseif ($type === VerbatimBlockParser::VERBATIM) {
                    $nodes[] = $this->makeVerbatimNode($current);
                } elseif ($type === 'comment') {
                    $nodes[] = $this->makeCommentNode($current);
                } elseif ($type === 'literal' || $type === 'newline') {
                    $nodes[] = $this->makeStaticNode($current);
                } elseif ($type === 'unsafe_echo') {
                    $nodes[] = $this->makeUnsafeEcho($current);
                } elseif ($type === 'echo') {
                    $nodes[] = $this->makeEchoNode($current);
                } elseif ($type === 'directive') {
                    if ($current['name'] == PhpBlockParser::NAME_PHP) {
                        $nodes[] = $this->makeSelfClosingPhpNode($current);
                    } else {
                        $newDirective = $this->makeNode($current);

                        $stack->push($newDirective);

                        // Here we attempt to locate a LanguageDirective definition for
                        // whatever is contained in this new directive node. If we
                        // find one, let's check if we need to apply stack data.
                        $languageDirective = $this->findLanguageDirective($current['name']);

                        if ($languageDirective !== null && $languageDirective->mustBeEnclosed()) {
                            $stackDetails = $stack->findParent($languageDirective);

                            if ($stackDetails !== null) {
                                $newDirective->parent = $stackDetails[0];
                                $newDirective->parentTypeIndex = $stackDetails[1];
                            }
                        }

                        $nodes[] = $newDirective;
                    }
                }
            }
        }

        return $this->makeNodeHierarchy($nodes);
    }

    private function makePhpNode($directive)
    {
        $phpNode = new PhpNode();

        $phpNode->start = $directive['start'];
        $phpNode->end = $directive['end'];
        $phpNode->content = $directive['content'];
        $phpNode->innerContent = $phpNode->content;
        $phpNode->rawContent = $directive['raw_content'];
        $phpNode->pairStart = $directive['raw_pair'][0];
        $phpNode->pairEnd = $directive['raw_pair'][1];
        $phpNode->directive = $directive['name'];

        $this->nodeReferences[] = $phpNode;

        return $phpNode;
    }

    private function makeVerbatimNode($directive)
    {
        $verbatim = new VerbatimNode();

        $verbatim->start = $directive['start'];
        $verbatim->end = $directive['end'];
        $verbatim->content = $directive['content'];
        $verbatim->innerContent = $verbatim->content;
        $verbatim->rawContent = $directive['raw_content'];
        $verbatim->pairStart = $directive['raw_pair'][0];
        $verbatim->pairEnd = $directive['raw_pair'][1];
        $verbatim->directive = $directive['name'];

        $this->nodeReferences[] = $verbatim;

        return $verbatim;
    }

    private function makeCommentNode($directive)
    {
        $comment = new CommentNode();

        $comment->innerContent = $directive['content'];
        $comment->rawContent = $comment->innerContent;
        $comment->start = $directive['start'];
        $comment->end = $directive['end'];

        $this->nodeReferences[] = $comment;

        return $comment;
    }

    private function makeStaticNode($directive)
    {
        $static = new StaticNode();

        $static->content = str_replace('@@', '@', $directive['content']);
        $static->rawContent = $directive['content'];
        $static->innerContent = $static->content;

        $this->nodeReferences[] = $static;

        return $static;
    }

    private function makeUnsafeEcho($details)
    {
        $node = new EchoNode();

        $node->start = $details['start'];
        $node->end = $details['end'];
        $node->directive = 'echo';
        $node->rawContent = $details['content'];
        $node->openCount = 3;
        $node->isSafe = false;

        $adjustedContent = ltrim($node->rawContent, '{');
        $adjustedContent = rtrim($adjustedContent, '}');
        $adjustedContent = rtrim($adjustedContent, '!');
        $adjustedContent = ltrim($adjustedContent, '!');

        $node->innerContent = $adjustedContent;

        $this->nodeReferences[] = $node;

        return $node;
    }

    private function makeEchoNode($details)
    {
        $node = new EchoNode();

        $node->start = $details['start'];
        $node->end = $details['end'];
        $node->directive = 'echo';
        $node->rawContent = $details['content'];
        $node->openCount = $details['open_count'];
        $node->isSafe = true;

        $adjustedContent = ltrim($node->rawContent, '{');
        $adjustedContent = rtrim($adjustedContent, '}');

        $node->innerContent = $adjustedContent;

        $this->nodeReferences[] = $node;

        return $node;
    }

    private function makeSelfClosingPhpNode($directive)
    {
        if (mb_strlen($directive['inner_content']) == 0) {
            return $this->makeStaticNode($directive);
        }

        $phpNode = new PhpNode();

        $phpNode->start = $directive['start'];
        $phpNode->end = $directive['end'];
        $phpNode->content = $directive['content'];
        $phpNode->innerContent = $directive['inner_content'];
        $phpNode->rawContent = $directive['content'];
        $phpNode->pairStart = null;
        $phpNode->pairEnd = null;
        $phpNode->directive = $directive['name'];
        $phpNode->isSelfClosing = true;

        $this->nodeReferences[] = $phpNode;

        return $phpNode;
    }

    private function makeNode($details)
    {
        $name = trim($details['name']);

        if ($name === 'extends') {
            $extendsNode = new ExtendsNode();

            $extendsNode->start = $details['start'];
            $extendsNode->end = $details['end'];
            $extendsNode->directive = 'extends';
            $extendsNode->rawContent = $details['content'];

            $innerContent = $details['inner_content'];
            $innerContent = ltrim($innerContent, '\'"');
            $innerContent = rtrim($innerContent, '\'"');

            $extendsNode->templateName = $innerContent;
            $extendsNode->innerContent = $details['inner_content'];

            $this->nodeReferences[] = $extendsNode;

            return $extendsNode;
        }

        return $this->makeBaseNode($details);
    }

    private function makeBaseNode($details)
    {
        $node = new DirectiveNode();

        $node->start = $details['start'];
        $node->end = $details['end'];
        $node->directive = trim($details['name']);
        $node->rawContent = $details['content'];
        $node->innerContent = $details['inner_content'];

        $this->nodeReferences[] = $node;

        return $node;
    }

    /**
     * Attempts to locate a LanguageDirective instance by directive name.
     *
     * @param  string  $name  The directive name.
     * @return LanguageDirective|null
     */
    private function findLanguageDirective($name)
    {
        $name = trim($name);

        if (array_key_exists($name, $this->languageDirectives)) {
            return $this->languageDirectives[$name];
        }

        return null;
    }

    /**
     * @param  Node[]  $nodes
     */
    private function makeNodeHierarchy($nodes, $parent = null)
    {
        $newNodes = [];

        for ($i = 0; $i < count($nodes); $i++) {
            $current = $nodes[$i];

            if ($current instanceof StaticNode || $current instanceof EchoNode) {
                $newNodes[] = $current;
            } else {
                $name = $current->directive;

                if ($this->directiveRequiresClose($name, $parent)) {
                    $scanDetails = $this->scanToClose($current, $nodes, $i);

                    $newNodes[] = $scanDetails[0];
                    $i += $scanDetails[1] + 1;
                } else {
                    $newNodes[] = $current;
                }
            }
        }

        return $newNodes;
    }

    private function directiveRequiresClose($name, $parent = null)
    {
        if (array_key_exists($name, $this->languageDirectives)) {
            /** @var LanguageDirective $directive */
            $directive = $this->languageDirectives[$name];

            if ($parent === null) {
                return $directive->isTagPair;
            } else {
                $parentName = '';

                if ($parent instanceof TagPairNode) {
                    $parentName = $parent->pairOpen->directive;
                }

                if (in_array($parentName, $directive->canAppearIn)) {
                    return false;
                } else {
                    return true;
                }
            }
        }

        return false;
    }

    private function scanToClose($node, $nodes, $startAt)
    {
        /** @var LanguageDirective $directive */
        $directive = $this->languageDirectives[$node->directive];
        $searchFor = $directive->isClosedBy;
        $canOpenThisClose = [];

        if (is_string($searchFor)) {
            $canOpenThisClose = $this->getTagsThatCanBeClosedBy($searchFor);
        } elseif (is_array($searchFor)) {
            foreach ($searchFor as $candidate) {
                $canOpenThisClose = array_merge($canOpenThisClose, $this->getTagsThatCanBeClosedBy($candidate));
            }
        }

        $canOpenThisClose = array_unique($canOpenThisClose);

        $pairOpen = $nodes[$startAt];
        $pairClose = null;

        $foundClose = false;

        $enclosed = [];
        $seekIndex = 1;
        $enclosedCount = 0;

        for ($i = ($startAt + 1); $i < count($nodes); $i++) {
            /** @var Node $current */
            $current = $nodes[$i];

            $name = $current->directive;

            if (in_array($name, $canOpenThisClose)) {
                $seekIndex += 1;
            }

            if (is_string($searchFor)) {
                if ($name === $searchFor) {
                    $seekIndex -= 1;
                }
            } elseif (is_array($searchFor)) {
                if (in_array($name, $searchFor)) {
                    $seekIndex -= 1;
                }
            }

            if ($seekIndex === 0) {
                $foundClose = true;
                $pairClose = $current;
                break;
            }

            $enclosed[] = $current;
        }

        $enclosedCount = count($enclosed);

        $pairNode = new TagPairNode();
        $pairNode->pairOpen = $pairOpen;
        $pairNode->pairClose = $pairClose;

        $pairNode->nodes = $this->makeNodeHierarchy($enclosed, $pairNode);

        return [
            $pairNode,
            $enclosedCount,
        ];
    }

    private function getTagsThatCanBeClosedBy($closedBy)
    {
        $canBeClosedBy = [];

        /** @var LanguageDirective $directive */
        foreach ($this->languageDirectives as $directive) {
            if (is_string($directive->isClosedBy)) {
                if ($directive->isClosedBy === $closedBy) {
                    $canBeClosedBy[] = $directive->name;
                }
            } elseif (is_array($directive->isClosedBy)) {
                foreach ($directive->isClosedBy as $possibleTagClose) {
                    if ($possibleTagClose === $closedBy) {
                        $canBeClosedBy[] = $directive->name;
                    }
                }
            }
        }

        return $canBeClosedBy;
    }
}
