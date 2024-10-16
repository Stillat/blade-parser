<?php

namespace Stillat\BladeParser\Parser;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Stillat\BladeParser\Compiler\CompilerServices\CoreDirectiveRetriever;
use Stillat\BladeParser\Compiler\CompilerServices\LiteralContentHelpers;
use Stillat\BladeParser\Compiler\CompilerServices\StringUtilities;
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Document\Structures\DirectiveClosingAnalyzer;
use Stillat\BladeParser\Errors\BladeError;
use Stillat\BladeParser\Errors\ConstructContext;
use Stillat\BladeParser\Errors\ErrorType;
use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\ArgumentContentType;
use Stillat\BladeParser\Nodes\ArgumentGroupNode;
use Stillat\BladeParser\Nodes\CommentNode;
use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Nodes\Concerns\InteractsWithBladeErrors;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\EchoNode;
use Stillat\BladeParser\Nodes\EchoType;
use Stillat\BladeParser\Nodes\LiteralNode;
use Stillat\BladeParser\Nodes\NodeIndexer;
use Stillat\BladeParser\Nodes\PhpBlockNode;
use Stillat\BladeParser\Nodes\PhpTagNode;
use Stillat\BladeParser\Nodes\PhpTagType;
use Stillat\BladeParser\Nodes\Position;
use Stillat\BladeParser\Nodes\VerbatimNode;

class DocumentParser extends AbstractParser
{
    use InteractsWithBladeErrors;

    const K_CHAR = 'char';

    const K_LINE = 'line';

    const C_StartPhp = '<?php';

    const C_StartPhpEcho = '<?=';

    const C_BladeCommentStart = '{{--';

    const C_BladeEcho = '{{';

    const C_BladeEchoThree = '{{{';

    const C_BladeRawEcho = '{!!';

    const C_BladeComponentStart = '<x-';

    const C_BladeComponentStartNamespaced = '<x:';

    const C_BladeClosingComponentStart = '</x-';

    const C_BladeClosingComponentStartNamespaced = '</x:';

    private array $bladeIndex = [];

    /** @var BladeError[] */
    private array $parseErrors = [];

    private bool $produceFinalNode = true;

    /**
     * @var AbstractNode[]
     */
    private array $nodes = [];

    private ComponentParser $componentParser;

    private int $components = 0;

    private int $customComponents = 0;

    private array $customComponentTags = [];

    private array $coreDirectives = [];

    private bool $onlyComponents = false;

    private array $ignoreDirectives = [];

    public function __construct()
    {
        $this->componentParser = new ComponentParser;
        $this->withCoreDirectives();
    }

    /**
     * Sets the custom directive names.
     *
     * @param  array  $names  The directive names.
     * @return $this
     */
    public function setDirectiveNames(array $names): DocumentParser
    {
        $this->customDirectives = $names;

        return $this;
    }

    /**
     * Retrieves a collection of parser errors.
     */
    public function getErrors(): Collection
    {
        return collect($this->parseErrors);
    }

    /**
     * Registers a single custom component tag name.
     *
     * @param  string  $tagName  The component tag name.
     * @return $this
     */
    public function registerCustomComponentTag(string $tagName): DocumentParser
    {
        $this->customComponentTags[] = mb_strtolower($tagName);

        return $this;
    }

    /**
     * Registers multiple custom component tag names.
     *
     * @param  array  $tagNames  The tag names.
     * @return $this
     */
    public function registerCustomComponentTags(array $tagNames): DocumentParser
    {
        $this->customComponentTags = array_merge($this->customComponentTags, $tagNames);

        return $this;
    }

    /**
     * Registers a single custom directive name.
     *
     * @param  string  $name  The directive name.
     */
    public function registerCustomDirective(string $name): void
    {
        $this->customDirectives[] = $name;
    }

    public function withCoreDirectives(): DocumentParser
    {
        $this->coreDirectives = array_merge(
            CoreDirectiveRetriever::instance()->getDirectiveNames(),
            CoreDirectives::BLADE_STRUCTURES,
        );

        return $this;
    }

    /**
     * Removes support for all core Blade directives.
     *
     * @return $this
     */
    public function withoutCoreDirectives(): DocumentParser
    {
        $this->coreDirectives = [];

        return $this;
    }

    public function onlyParseComponents(bool $onlyComponents = true): DocumentParser
    {
        $this->onlyComponents = $onlyComponents;

        return $this;
    }

    public function ignoreDirectives(array $directives): DocumentParser
    {
        $this->ignoreDirectives = $directives;

        return $this;
    }

    public function toDocument(bool $resolveStructures = true): Document
    {
        $document = new Document;
        $document->syncFromParser($this);

        if ($resolveStructures) {
            $document->resolveStructures();
        }

        return $document;
    }

    /**
     * Retrieves a list of directive names supported by the parser instance.
     */
    public function getDirectiveNames(): array
    {
        return array_diff(array_merge(
            $this->coreDirectives,
            $this->customDirectives
        ), $this->ignoreDirectives);
    }

    /**
     * Returns a value indicating if any Laravel tag components were parsed.
     */
    public function hasComponents(): bool
    {
        return $this->components > 0;
    }

    /**
     * Returns a value indicating if any custom tag components were parsed.
     */
    public function hasCustomComponents(): bool
    {
        return $this->customComponents > 0;
    }

    /**
     * Returns an list of all custom component tag names.
     *
     * @return string[]
     */
    public function getCustomComponentTags(): array
    {
        return $this->customComponentTags;
    }

    /**
     * Returns a value indicating if any component tags were parsed.
     */
    public function hasAnyComponents(): bool
    {
        return $this->components > 0 || $this->customComponents > 0;
    }

    protected function lineColumnFromOffset($offset): array
    {
        if (count($this->documentOffsets) == 0) {
            return [
                self::K_LINE => 1,
                self::K_CHAR => $offset + 1,
            ];
        }

        $lineToUse = 0;
        $charToUse = 0;

        if (! array_key_exists($offset, $this->documentOffsets)) {
            $nearestOffset = null;
            $nearestOffsetIndex = null;

            foreach ($this->documentOffsets as $documentOffset => $details) {
                if ($documentOffset >= $offset) {
                    $nearestOffset = $details;
                    $nearestOffsetIndex = $documentOffset;
                    break;
                }
            }

            if ($nearestOffset != null) {
                $offsetDelta = $nearestOffset[self::K_CHAR] - $nearestOffsetIndex + $offset;
                $charToUse = $offsetDelta;
                $lineToUse = $nearestOffset[self::K_LINE];
            } else {
                $lastOffsetKey = array_key_last($this->documentOffsets);
                $lastOffset = $this->documentOffsets[$lastOffsetKey];
                $lineToUse = $lastOffset['line'] + 1;
                $charToUse = $offset - $lastOffsetKey;
            }
        } else {
            $details = $this->documentOffsets[$offset];

            $lineToUse = $details[self::K_LINE];
            $charToUse = $details[self::K_CHAR];
        }

        return [
            self::K_LINE => $lineToUse,
            self::K_CHAR => $charToUse,
        ];
    }

    protected function prepareInput(string $content): void
    {
        $this->content = StringUtilities::normalizeLineEndings($content);
        $this->inputLen = mb_strlen($this->content);

        // The document content was normalized, so we can search for "\n".
        preg_match_all('/\n/', $this->content, $documentNewLines, PREG_OFFSET_CAPTURE);
        $newLineCountLen = count($documentNewLines[0]);

        $currentLine = $this->seedStartLine;
        $lastOffset = null;
        for ($i = 0; $i < $newLineCountLen; $i++) {
            $thisNewLine = $documentNewLines[0][$i];
            $thisIndex = $thisNewLine[1];
            $indexChar = $thisIndex;

            if ($lastOffset != null) {
                $indexChar = $thisIndex - $lastOffset;
            } else {
                $indexChar = $indexChar + 1;
            }

            $this->documentOffsets[$thisIndex] = [
                self::K_CHAR => $indexChar,
                self::K_LINE => $currentLine,
            ];

            $currentLine += 1;
            $lastOffset = $thisIndex;
        }

        $customComponentPattern = '';

        if (count($this->customComponentTags) > 0) {
            $customComponentPatterns = [];

            foreach ($this->customComponentTags as $tagName) {
                $customComponentPatterns[] = "<{$tagName}-";
                $customComponentPatterns[] = "<\/{$tagName}-";
                $customComponentPatterns[] = "<{$tagName}:";
                $customComponentPatterns[] = "<\/{$tagName}:";
            }

            $customComponentPattern = '|'.implode('|', $customComponentPatterns);
        }

        $componentPattern = '<x-|<\/x-|<x:|<\/x:'.$customComponentPattern;
        $generalBladePattern = '@?{{--|@?{{{|@?{{|<\?php|<\?=|@?{!!|@+|'.$componentPattern;

        if ($this->onlyComponents) {
            $generalBladePattern = $componentPattern;
        }

        preg_match_all('/('.$generalBladePattern.')/m', $this->content, $bladeCandidates, PREG_OFFSET_CAPTURE);

        if (! is_array($bladeCandidates) || count($bladeCandidates) != 2) {
            return;
        }

        $bladeCandidates = $bladeCandidates[0];

        // Convert our regex offsets to multibyte offsets.
        $lastOffset = 0;

        for ($i = 0; $i < count($bladeCandidates); $i++) {
            $candidate = $bladeCandidates[$i];

            if ($candidate[1] == 0) {
                continue;
            }

            $offset = mb_strpos($this->content, $candidate[0], $lastOffset + 1);

            if ($offset === false) {
                $offset = $candidate[1];
            }
            $bladeCandidates[$i][1] = $offset;
            $candidate[1] = $offset;
            $lastOffset = $offset + 1;
        }

        $directiveNames = collect($this->getDirectiveNames())->map(function ($name) {
            return mb_strtolower($name);
        })->flip()->all();

        foreach ($bladeCandidates as $candidate) {
            $matchText = $candidate[0];
            $matchOffset = $candidate[1];

            if ($matchText == self::C_BladeCommentStart) {
                $commentEntry = new IndexElement;
                $commentEntry->type = IndexElementType::BladeComment;
                $commentEntry->startOffset = $matchOffset;
                $commentEntry->content = $matchText;

                $this->bladeIndex[] = $commentEntry;

                continue;
            } elseif ($matchText == self::C_AtChar) {
                $result = $this->fetchAt($matchOffset, 2);

                if (mb_strlen($result) < 2) {
                    continue;
                }

                $nextChar = mb_substr($result, 1, 1);

                if (! ctype_alpha($nextChar) && $nextChar != '_') {
                    continue;
                }

                $content = $this->fetchDirectiveNameAt($matchOffset + 1);

                if (! array_key_exists(mb_strtolower($content), $directiveNames)) {
                    continue;
                }

                $directiveEntry = new IndexElement;
                $directiveEntry->type = IndexElementType::Directive;
                $directiveEntry->content = $content;
                $directiveEntry->startOffset = $matchOffset;

                $this->bladeIndex[] = $directiveEntry;

                continue;
            } elseif ($matchText == self::C_StartPhp) {
                $phpEntry = new IndexElement;
                $phpEntry->type = IndexElementType::PhpOpenTag;
                $phpEntry->content = self::C_StartPhp;
                $phpEntry->startOffset = $matchOffset;

                $this->bladeIndex[] = $phpEntry;

                continue;
            } elseif ($matchText == self::C_StartPhpEcho) {
                $phpEntry = new IndexElement;
                $phpEntry->type = IndexElementType::PhpOpenTagWithEcho;
                $phpEntry->content = self::C_StartPhpEcho;
                $phpEntry->startOffset = $matchOffset;

                $this->bladeIndex[] = $phpEntry;

                continue;
            } elseif (Str::startsWith($matchText, self::C_AtChar)) {
                // Just ignore these because they will be escaped content.
                continue;
            } elseif ($matchText == self::C_BladeRawEcho) {
                $bladeEntry = new IndexElement;
                $bladeEntry->type = IndexElementType::BladeRawEcho;
                $bladeEntry->content = self::C_BladeRawEcho;
                $bladeEntry->startOffset = $matchOffset;

                $content = $this->fetchAlphaNumericAt($matchOffset + 1);

                if ($content == self::C_LeftCurlyBracket) {
                    continue;
                }

                $this->bladeIndex[] = $bladeEntry;

                continue;
            } elseif ($matchText == self::C_BladeEchoThree) {
                $bladeEntry = new IndexElement;
                $bladeEntry->type = IndexElementType::BladeEchoThree;
                $bladeEntry->content = self::C_BladeEchoThree;
                $bladeEntry->startOffset = $matchOffset;

                $this->bladeIndex[] = $bladeEntry;

                continue;
            } elseif ($matchText == self::C_BladeEcho) {
                $bladeEntry = new IndexElement;
                $bladeEntry->type = IndexElementType::BladeEcho;
                $bladeEntry->content = self::C_BladeEcho;
                $bladeEntry->startOffset = $matchOffset;

                $this->bladeIndex[] = $bladeEntry;

                continue;
            } elseif ($matchText == self::C_BladeComponentStart || $matchText == self::C_BladeComponentStartNamespaced) {
                $componentEntry = new IndexElement;
                $componentEntry->type = IndexElementType::ComponentOpenTag;
                $componentEntry->content = $matchText;
                $componentEntry->startOffset = $matchOffset;

                $this->bladeIndex[] = $componentEntry;

                continue;
            } elseif ($matchText == self::C_BladeClosingComponentStart || $matchText == self::C_BladeClosingComponentStartNamespaced) {
                $componentEntry = new IndexElement;
                $componentEntry->type = IndexElementType::ComponentClosingTag;
                $componentEntry->content = $matchText;
                $componentEntry->startOffset = $matchOffset;

                $this->bladeIndex[] = $componentEntry;

                continue;
            } elseif (Str::startsWith($matchText, '</') && $this->isTagCloseCustomComponent($matchText)) {
                $customComponentClose = new IndexElement;
                $customComponentClose->type = IndexElementType::CustomComponentClosingTag;
                $customComponentClose->content = $matchText;
                $customComponentClose->startOffset = $matchOffset;

                $this->bladeIndex[] = $customComponentClose;

                continue;
            } elseif (Str::startsWith($matchText, '<') && $this->isTagStartCustomComponent($matchText)) {
                $customComponentEntry = new IndexElement;
                $customComponentEntry->type = IndexElementType::CustomComponentOpenTag;
                $customComponentEntry->content = $matchText;
                $customComponentEntry->startOffset = $matchOffset;

                $this->bladeIndex[] = $customComponentEntry;

                continue;
            }
        }
    }

    private function isTagCloseCustomComponent(string $tag): bool
    {
        return in_array(mb_strtolower(mb_substr($tag, 2, -1)), $this->customComponentTags);
    }

    private function isTagStartCustomComponent(string $tag): bool
    {
        return in_array(mb_strtolower(mb_substr($tag, 1, -1)), $this->customComponentTags);
    }

    private function makeLiteralNode($start, $end): ?LiteralNode
    {
        if (count($this->nodes) > 0) {
            $last = $this->nodes[count($this->nodes) - 1];

            if ($last->position != null) {
                if ($start < $last->position->endOffset) {
                    $start = $last->position->endOffset + 1;
                    if ($start > $end || $start == $end) {
                        return null;
                    }
                }
            }
        }
        $content = mb_substr($this->content, $start, $end - $start);

        $bladeLiteralNode = new LiteralNode;
        $bladeLiteralNode->position = $this->makePosition($start, $start + mb_strlen($content) - 1);
        $bladeLiteralNode->content = $content;
        $bladeLiteralNode->originalLeadingWhitespace = StringUtilities::extractLeadingWhitespace($content);
        $bladeLiteralNode->originalTrailingWhitespace = StringUtilities::extractTrailingWhitespace($content);

        $bladeLiteralNode->unescapedContent = LiteralContentHelpers::getUnescapedContent($content);

        return $bladeLiteralNode;
    }

    private function makeCustomComponentNode(string $type, int $startLocation, string $content): ComponentNode
    {
        $isClosing = false;
        if (Str::startsWith($type, '</')) {
            $isClosing = true;
            $type = mb_substr($type, 2, -1);
        } else {
            $type = mb_substr($type, 1, -1);
        }

        $innerContent = $content;
        $typeLen = mb_strlen($type);

        $componentNode = new ComponentNode;
        $componentNode->isCustomComponent = true;
        $componentNode->componentPrefix = $type;
        $componentNode->content = $content;
        $componentNode->position = $this->makePosition($startLocation, $startLocation + mb_strlen($content) - 1);
        $componentNode->isClosingTag = $isClosing;

        if ($isClosing) {
            $innerContent = mb_substr($innerContent, $typeLen + 3);
        } else {
            $innerContent = mb_substr($innerContent, $typeLen + 2);
        }

        if (Str::endsWith($content, '/>')) {
            $componentNode->isSelfClosing = true;
            $innerContent = mb_substr($innerContent, 0, -2);
        } else {
            $componentNode->isSelfClosing = false;
            $innerContent = mb_substr($innerContent, 0, -1);
        }

        $componentNode->innerContent = $innerContent;

        $this->componentParser->parse($componentNode);

        $this->customComponents += 1;

        return $componentNode;
    }

    private function makeComponentNode(int $startLocation, string $content): ComponentNode
    {
        $componentNode = new ComponentNode;
        $componentNode->content = $content;
        $componentNode->position = $this->makePosition($startLocation, $startLocation + mb_strlen($content) - 1);

        $innerContent = $content;

        if (Str::startsWith($innerContent, [self::C_BladeClosingComponentStartNamespaced, self::C_BladeClosingComponentStart])) {
            $componentNode->isClosingTag = true;
            $innerContent = mb_substr($innerContent, 4);
        } else {
            $componentNode->isClosingTag = false;
            $innerContent = mb_substr($innerContent, 3);
        }

        if (Str::endsWith($content, '/>')) {
            $componentNode->isSelfClosing = true;
            $componentNode->isClosingTag = true;
            $innerContent = mb_substr($innerContent, 0, -2);
        } else {
            $componentNode->isSelfClosing = false;
            $innerContent = mb_substr($innerContent, 0, -1);
        }

        $componentNode->innerContent = $innerContent;

        $this->componentParser->parse($componentNode);
        $this->components += 1;

        return $componentNode;
    }

    public function parseTemplate(string $document): static
    {
        $this->parse($document);

        return $this;
    }

    /**
     * Parses the input document and returns an array of nodes.
     *
     * @param  string  $document  The input document.
     * @return AbstractNode[]
     */
    public function parse(string $document): array
    {
        $this->produceFinalNode = true;
        $this->originalContent = $document;
        $this->components = 0;
        $this->bladeIndex = [];
        $this->nodes = [];
        $this->parseErrors = [];
        $this->prepareInput($document);

        /** @var AbstractNode $lastNode */
        $lastNode = null;

        $indexLen = count($this->bladeIndex);

        if ($indexLen == 0) {
            if ($literal = $this->makeLiteralNode(0, $this->inputLen)) {
                $this->nodes[] = $literal;
            }

            $this->fillNodesLineAndColumnNumbers($this->nodes);

            return $this->nodes;
        }

        if ($indexLen > 0 && $this->bladeIndex[0]->startOffset > 0) {
            if ($literal = $this->makeLiteralNode(0, $this->bladeIndex[0]->startOffset)) {
                $this->nodes[] = $literal;
            }
        }

        /** @var IndexElement $indexEntry */
        for ($i = 0; $i < $indexLen; $i++) {
            $indexEntry = $this->bladeIndex[$i];

            if ($lastNode != null && $lastNode->position != null && $indexEntry->startOffset < $lastNode->position->endOffset) {
                // Skip this entry because it is inside the last node.
                continue;
            }

            if ($lastNode != null && $lastNode->position != null && ($indexEntry->startOffset - $lastNode->position->endOffset - 1) > 0) {
                if ($literal = $this->makeLiteralNode($lastNode->position->endOffset + 1, $indexEntry->startOffset)) {
                    $this->nodes[] = $literal;
                }
            }

            if ($indexEntry->type == IndexElementType::BladeComment) {
                $commentContent = $this->scanToEndOfComment($indexEntry->startOffset);

                if ($commentContent == null) {
                    $this->pushParseError($i, $indexEntry->startOffset, 4, ErrorType::UnexpectedEndOfInput, ConstructContext::Comment);

                    continue;
                }

                $commentNode = new CommentNode;
                $commentNode->position = $this->makePosition($indexEntry->startOffset, $indexEntry->startOffset + mb_strlen($commentContent->content) - 1);
                $commentNode->content = $commentContent->content;
                $commentNode->innerContent = mb_substr($commentNode->content, 4, -4);
                $commentNode->originalLeadingWhitespace = StringUtilities::extractLeadingWhitespace($commentNode->innerContent);
                $commentNode->originalTrailingWhitespace = StringUtilities::extractTrailingWhitespace($commentNode->innerContent);

                $this->nodes[] = $commentNode;
                $lastNode = $commentNode;

                continue;
            } elseif ($indexEntry->type == IndexElementType::CustomComponentOpenTag || $indexEntry->type == IndexElementType::CustomComponentClosingTag) {
                $result = $this->scanToEndOfComponentTag($indexEntry->startOffset);
                if ($result == null) {
                    $this->pushParseError($i, $indexEntry->startOffset, 3, ErrorType::UnexpectedEndOfInput, ConstructContext::ComponentTag);

                    continue;
                } elseif ($result->didAbandon) {
                    $this->pushParseError($i, $indexEntry->startOffset, 3, $result->abandonReason, ConstructContext::ComponentTag);

                    continue;
                }

                $node = $this->makeCustomComponentNode($indexEntry->content, $indexEntry->startOffset, $result->content);

                $this->nodes[] = $node;
                $lastNode = $node;

                continue;
            } elseif ($indexEntry->type == IndexElementType::ComponentOpenTag || $indexEntry->type == IndexElementType::ComponentClosingTag) {
                $result = $this->scanToEndOfComponentTag($indexEntry->startOffset);

                if ($result == null) {
                    $this->pushParseError($i, $indexEntry->startOffset, 3, ErrorType::UnexpectedEndOfInput, ConstructContext::ComponentTag);

                    continue;
                } elseif ($result->didAbandon) {
                    $this->pushParseError($i, $indexEntry->startOffset, 3, $result->abandonReason, ConstructContext::ComponentTag);

                    continue;
                }

                $node = $this->makeComponentNode($indexEntry->startOffset, $result->content);

                $this->nodes[] = $node;
                $lastNode = $node;

                continue;
            } elseif ($indexEntry->type == IndexElementType::Directive) {
                $offset = $indexEntry->startOffset + mb_strlen($indexEntry->content) + 1;
                $nextNonWs = $this->fetchNextNonWhitespaceAt($offset);
                $argumentGroupContent = null;

                if ($nextNonWs != null && $nextNonWs->content == self::C_LeftBracket) {
                    $argumentGroupContent = $this->scanToEndOfArgumentGroup($nextNonWs->offset);
                }

                if ($indexEntry->content == BladeKeywords::K_Verbatim) {
                    $endVerbatim = null;
                    $skipTo = $i;

                    for ($j = $i + 1; $j < $indexLen; $j++) {
                        /** @var IndexElement $checkEntry */
                        $checkEntry = $this->bladeIndex[$j];

                        if ($checkEntry->type == IndexElementType::Directive && $checkEntry->content == BladeKeywords::K_EndVerbatim) {
                            $skipTo = $j;
                            $endVerbatim = $checkEntry;
                            break;
                        }
                    }

                    if ($endVerbatim != null) {
                        $verbatimNode = new VerbatimNode;
                        $verbatimNode->position = $this->makePosition($indexEntry->startOffset, $endVerbatim->startOffset + mb_strlen($endVerbatim->content));
                        $verbatimNode->content = mb_substr($this->content, $indexEntry->startOffset, $verbatimNode->position->endOffset - $indexEntry->startOffset + 1);
                        $verbatimNode->innerContent = mb_substr($verbatimNode->content, 9, -12);
                        $verbatimNode->originalLeadingWhitespace = StringUtilities::extractLeadingWhitespace($verbatimNode->innerContent);
                        $verbatimNode->originalTrailingWhitespace = StringUtilities::extractTrailingWhitespace($verbatimNode->innerContent);

                        $i = $skipTo;
                        $this->nodes[] = $verbatimNode;
                        $lastNode = $verbatimNode;

                        continue;
                    } else {
                        $this->pushParseError($i, $indexEntry->startOffset, 8, ErrorType::UnexpectedEndOfInput, ConstructContext::Verbatim);

                        continue;
                    }
                }

                // Process @php @endphp blocks.
                if ($indexEntry->content == BladeKeywords::K_Php && $argumentGroupContent == null) {
                    // Scan the index to find the endphp directive.
                    $endPhpEntry = null;
                    $skipTo = $i;

                    for ($j = $i + 1; $j < $indexLen; $j++) {
                        /** @var IndexElement $checkEntry */
                        $checkEntry = $this->bladeIndex[$j];

                        if ($checkEntry->type == IndexElementType::Directive && $checkEntry->content == BladeKeywords::K_EndPhp) {
                            $skipTo = $j;
                            $endPhpEntry = $checkEntry;
                            break;
                        }
                    }

                    if ($endPhpEntry != null) {
                        $bladePhpNode = new PhpBlockNode;
                        $bladePhpNode->position = $this->makePosition($indexEntry->startOffset, $endPhpEntry->startOffset + mb_strlen($endPhpEntry->content));
                        $bladePhpNode->content = mb_substr($this->content, $indexEntry->startOffset, $bladePhpNode->position->endOffset - $indexEntry->startOffset + 1);
                        $bladePhpNode->innerContent = mb_substr($bladePhpNode->content, 4, -7);
                        $bladePhpNode->originalLeadingWhitespace = StringUtilities::extractLeadingWhitespace($bladePhpNode->innerContent);
                        $bladePhpNode->originalTrailingWhitespace = StringUtilities::extractTrailingWhitespace($bladePhpNode->innerContent);

                        $i = $skipTo;
                        $this->nodes[] = $bladePhpNode;
                        $lastNode = $bladePhpNode;

                        continue;
                    } else {
                        // Manually construct the error type here to not duplicate @php directives.
                        $this->parseErrors[] = new BladeError($this->makePosition($indexEntry->startOffset, $indexEntry->startOffset + 3), ErrorType::UnexpectedEndOfInput, ConstructContext::BladePhpBlock);
                    }
                }

                $directiveNode = new DirectiveNode;

                $directiveStart = $indexEntry->startOffset;
                $directiveNameEnd = $directiveStart + mb_strlen($indexEntry->content);
                $directiveEnd = $directiveStart + mb_strlen($indexEntry->content);

                if ($argumentGroupContent != null) {
                    $argNode = new ArgumentGroupNode($directiveNode);
                    $argNode->position = $this->makePosition($argumentGroupContent->offset, $argumentGroupContent->offset + mb_strlen($argumentGroupContent->content) - 1);
                    $argNode->content = $argumentGroupContent->content;
                    $argNode->innerContent = mb_substr($argNode->content, 1, -1);

                    if (Str::startsWith($argNode->innerContent, self::C_LeftCurlyBracket) && Str::endsWith($argNode->innerContent, self::C_RightCurlyBracket)) {
                        $argNode->contentType = ArgumentContentType::Json;
                    }

                    $directiveEnd = $argNode->position->endOffset;
                    $directiveNode->arguments = $argNode;
                }

                $directiveNode->position = $this->makePosition($directiveStart, $directiveEnd);
                $directiveNode->directiveNamePosition = $this->makePosition($directiveStart, $directiveNameEnd);
                $directiveNode->content = $indexEntry->content;
                $directiveNode->sourceContent = mb_substr($this->content, $directiveStart, $directiveEnd - $directiveStart + 1);

                DirectiveClosingAnalyzer::analyze($directiveNode);

                $this->nodes[] = $directiveNode;

                $lastNode = $directiveNode;

                continue;
            } elseif ($indexEntry->type == IndexElementType::PhpOpenTag || $indexEntry->type == IndexElementType::PhpOpenTagWithEcho) {
                $phpTagNode = new PhpTagNode;
                $phpContent = $this->scanToEndOfPhp($indexEntry->startOffset);

                // We will just assume that the PHP content continues to the end of the document.
                if ($phpContent == null) {
                    $phpContent = new ScanResult;
                    $phpContent->offset = $indexEntry->startOffset;
                    $phpContent->content = mb_substr($this->content, $indexEntry->startOffset);

                    $shiftOffset = 4;
                    $context = ConstructContext::PhpOpen;

                    if ($indexEntry->type == IndexElementType::PhpOpenTagWithEcho) {
                        $shiftOffset = 3;
                        $context = ConstructContext::PhpShortOpen;
                    }

                    // Push what will effectively be a warning.
                    $this->parseErrors[] = new BladeError($this->makePosition($indexEntry->startOffset, $indexEntry->startOffset + $shiftOffset), ErrorType::UnexpectedEndOfInput, $context);
                }

                // The -1 is to account for the content containing the leading <.
                $phpTagNode->position = $this->makePosition($indexEntry->startOffset, $phpContent->offset + mb_strlen($phpContent->content) - 1);
                $phpTagNode->content = $phpContent->content;

                if ($indexEntry->type == IndexElementType::PhpOpenTag) {
                    $phpTagNode->type = PhpTagType::PhpOpenTag;
                    $innerContent = $phpTagNode->content;
                    $innerContent = mb_substr($innerContent, 5);
                    $innerContent = mb_substr($innerContent, 0, -2);
                    $phpTagNode->innerContent = $innerContent;
                } else {
                    $phpTagNode->type = PhpTagType::PhpOpenTagWithEcho;
                    $innerContent = $phpTagNode->content;
                    $innerContent = mb_substr($innerContent, 3);
                    $innerContent = mb_substr($innerContent, 0, -2);
                    $phpTagNode->innerContent = $innerContent;
                }

                $phpTagNode->originalLeadingWhitespace = StringUtilities::extractLeadingWhitespace($phpTagNode->innerContent);
                $phpTagNode->originalTrailingWhitespace = StringUtilities::extractTrailingWhitespace($phpTagNode->innerContent);

                $this->nodes[] = $phpTagNode;
                $lastNode = $phpTagNode;

                continue;
            } elseif ($indexEntry->type == IndexElementType::BladeRawEcho) {
                $echoContent = $this->scanToEndOfRawEcho($indexEntry->startOffset);

                if ($echoContent == null) {
                    $this->pushParseError($i, $indexEntry->startOffset, 3, ErrorType::UnexpectedEndOfInput, ConstructContext::RawEcho);

                    continue;
                } elseif ($echoContent->didAbandon) {
                    $this->pushParseError($i, $indexEntry->startOffset, 3, $echoContent->abandonReason, ConstructContext::RawEcho);

                    continue;
                }

                $echoNode = new EchoNode;
                $echoNode->type = EchoType::RawEcho;
                $echoNode->position = $this->makePosition($indexEntry->startOffset, $echoContent->offset + mb_strlen($echoContent->content) - 1);
                $echoNode->content = $echoContent->content;
                $echoNode->innerContent = mb_substr($echoContent->content, 3, -3);

                $this->nodes[] = $echoNode;
                $lastNode = $echoNode;

                continue;
            } elseif ($indexEntry->type == IndexElementType::BladeEchoThree) {
                $echoContent = $this->scanToEndOfTripleEcho($indexEntry->startOffset);

                if ($echoContent == null) {
                    $this->pushParseError($i, $indexEntry->startOffset, 3, ErrorType::UnexpectedEndOfInput, ConstructContext::TripleEcho);

                    continue;
                } elseif ($echoContent->didAbandon) {
                    $this->pushParseError($i, $indexEntry->startOffset, 3, $echoContent->abandonReason, ConstructContext::TripleEcho);

                    continue;
                }

                $echoNode = new EchoNode;
                $echoNode->type = EchoType::TripleEcho;
                $echoNode->position = $this->makePosition($indexEntry->startOffset, $echoContent->offset + mb_strlen($echoContent->content) - 1);
                $echoNode->content = $echoContent->content;
                $echoNode->innerContent = mb_substr($echoContent->content, 3, -3);

                $this->nodes[] = $echoNode;
                $lastNode = $echoNode;

                continue;
            } elseif ($indexEntry->type == IndexElementType::BladeEcho) {
                $echoContent = $this->scanToEndOfEcho($indexEntry->startOffset);

                if ($echoContent == null) {
                    $this->pushParseError($i, $indexEntry->startOffset, 2, ErrorType::UnexpectedEndOfInput, ConstructContext::Echo);

                    continue;
                } elseif ($echoContent->didAbandon) {
                    $this->pushParseError($i, $indexEntry->startOffset, 2, $echoContent->abandonReason, ConstructContext::Echo);

                    continue;
                }

                $echoNode = new EchoNode;
                $echoNode->type = EchoType::Echo;
                $echoNode->position = $this->makePosition($indexEntry->startOffset, $echoContent->offset + mb_strlen($echoContent->content) - 1);
                $echoNode->content = $echoContent->content;
                $echoNode->innerContent = mb_substr($echoContent->content, 2, -2);

                $this->nodes[] = $echoNode;
                $lastNode = $echoNode;

                continue;
            }
        }

        $this->clearParserState();

        if ($this->produceFinalNode && $lastNode != null && $lastNode->position != null && $lastNode->position->endOffset < $this->inputLen - 1) {
            if ($literal = $this->makeLiteralNode($lastNode->position->endOffset + 1, $this->inputLen)) {
                $this->nodes[] = $literal;
            }
        }

        NodeIndexer::indexNodes($this->nodes);

        $this->fillNodesLineAndColumnNumbers($this->nodes);

        if (count($this->parseErrors) > 0) {
            foreach ($this->parseErrors as $error) {
                $this->fillLineAndColumnNumber($error->position);
            }
        }

        $this->createRelationships();

        return $this->nodes;
    }

    private function createRelationships(): void
    {
        $previous = null;
        $nodeCount = count($this->nodes);

        for ($i = 0; $i < $nodeCount; $i++) {
            $node = $this->nodes[$i];
            $next = null;
            if ($i + 1 < $nodeCount) {
                $next = $this->nodes[$i + 1];
            }

            $node->previousNode = $previous;
            $node->nextNode = $next;

            if ($previous == null) {
                $node->hasWhitespaceOnLeft = 0;
            } else {
                if ($previous instanceof LiteralNode) {
                    if (mb_strlen($previous->content) > 0 && mb_strlen(trim($previous->content)) == 0) {
                        $node->hasWhitespaceOnLeft = true;
                    } else {
                        if (Str::endsWith($previous->content, "\n")) {
                            $node->hasWhitespaceOnLeft = true;
                        } else {
                            $node->hasWhitespaceOnLeft = mb_strlen($previous->originalTrailingWhitespace) > 0;
                        }
                    }
                }
            }

            if ($next == null) {
                $node->hasWhitespaceToRight = 0;
            } else {
                if ($next instanceof LiteralNode) {
                    if (mb_strlen($next->content) > 0 && mb_strlen(trim($next->content)) == 0) {
                        $node->hasWhitespaceToRight = true;
                    } else {
                        if (Str::startsWith($next->content, "\n")) {
                            $node->hasWhitespaceToRight = true;
                        } else {
                            $node->hasWhitespaceToRight = mb_strlen($next->originalLeadingWhitespace) > 0;
                        }
                    }
                }
            }

            $previous = $node;
        }
    }

    private function pushParseError(int $indexOffset, int $offset, int $shiftEnd, ErrorType $type, ConstructContext $context)
    {
        $this->parseErrors[] = new BladeError(
            $this->makePosition($offset, $offset + $shiftEnd),
            $type,
            $context
        );

        $indexEntry = $this->bladeIndex[$indexOffset];

        if ($indexOffset == count($this->bladeIndex) - 1) {
            if ($literal = $this->makeLiteralNode($indexEntry->startOffset, $this->inputLen)) {
                $this->nodes[] = $literal;
            }
            $this->produceFinalNode = false;
        } else {
            if ($literal = $this->makeLiteralNode($indexEntry->startOffset, $this->bladeIndex[$indexOffset + 1]->startOffset)) {
                $this->nodes[] = $literal;
            }
        }
    }

    private function fillNodesLineAndColumnNumbers(array $nodes): void
    {
        /** @var AbstractNode $node */
        foreach ($nodes as $node) {
            $this->fillLineAndColumnNumber($node->position);

            if ($node instanceof DirectiveNode && $node->arguments != null) {
                $this->fillLineAndColumnNumber($node->arguments->position);
            }

            if ($node instanceof ComponentNode) {
                if ($node->namePosition != null) {
                    $this->fillLineAndColumnNumber($node->namePosition);
                }

                if ($node->parameterContentPosition != null) {
                    $this->fillLineAndColumnNumber($node->parameterContentPosition);
                }

                if (count($node->parameters) > 0) {
                    foreach ($node->parameters as $param) {
                        $this->fillLineAndColumnNumber($param->position);

                        if ($param->nameNode != null && $param->position != null) {
                            $this->fillLineAndColumnNumber($param->nameNode->position);
                        }

                        if ($param->valueNode != null && $param->position != null) {
                            $this->fillLineAndColumnNumber($param->valueNode->position);
                        }
                    }
                }
            }
        }
    }

    private function fillLineAndColumnNumber(Position $position): void
    {
        $startLineCol = $this->lineColumnFromOffset($position->startOffset);
        $position->startLine = $startLineCol[self::K_LINE];
        $position->startColumn = $startLineCol[self::K_CHAR];

        $endLineCol = $this->lineColumnFromOffset($position->endOffset);
        $position->endLine = $endLineCol[self::K_LINE];
        $position->endColumn = $endLineCol[self::K_CHAR];
    }

    /**
     * Retrieves the parsed nodes.
     *
     * @return AbstractNode[]
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }
}
