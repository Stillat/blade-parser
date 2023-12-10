<?php

namespace Stillat\BladeParser\Compiler;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ReflectsClosures;
use Stillat\BladeParser\Compiler\CompilerServices\LoopVariablesExtractor;
use Stillat\BladeParser\Compiler\CompilerServices\StringUtilities;
use Stillat\BladeParser\Compiler\Concerns\CompilesAuthorizations;
use Stillat\BladeParser\Compiler\Concerns\CompilesClasses;
use Stillat\BladeParser\Compiler\Concerns\CompilesComponents;
use Stillat\BladeParser\Compiler\Concerns\CompilesConditionals;
use Stillat\BladeParser\Compiler\Concerns\CompilesCustomDirectives;
use Stillat\BladeParser\Compiler\Concerns\CompilesEchos;
use Stillat\BladeParser\Compiler\Concerns\CompilesErrors;
use Stillat\BladeParser\Compiler\Concerns\CompilesFragments;
use Stillat\BladeParser\Compiler\Concerns\CompilesHelpers;
use Stillat\BladeParser\Compiler\Concerns\CompilesIncludes;
use Stillat\BladeParser\Compiler\Concerns\CompilesInjections;
use Stillat\BladeParser\Compiler\Concerns\CompilesJs;
use Stillat\BladeParser\Compiler\Concerns\CompilesJson;
use Stillat\BladeParser\Compiler\Concerns\CompilesLayouts;
use Stillat\BladeParser\Compiler\Concerns\CompilesLoops;
use Stillat\BladeParser\Compiler\Concerns\CompilesRawPhp;
use Stillat\BladeParser\Compiler\Concerns\CompilesStacks;
use Stillat\BladeParser\Compiler\Concerns\CompilesStyles;
use Stillat\BladeParser\Compiler\Concerns\CompilesTranslations;
use Stillat\BladeParser\Compiler\Concerns\CompilesVerbatim;
use Stillat\BladeParser\Compiler\Concerns\ManagesCustomConditions;
use Stillat\BladeParser\Compiler\Concerns\ManagesCustomDirectives;
use Stillat\BladeParser\Compiler\Transformers\RawTransformer;
use Stillat\BladeParser\Contracts\CustomComponentTagCompiler;
use Stillat\BladeParser\Errors\Exceptions\CompilationException;
use Stillat\BladeParser\Errors\Exceptions\UnsupportedNodeException;
use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\CommentNode;
use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Nodes\Concerns\InteractsWithBladeErrors;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\EchoNode;
use Stillat\BladeParser\Nodes\EchoType;
use Stillat\BladeParser\Nodes\LiteralNode;
use Stillat\BladeParser\Nodes\PhpBlockNode;
use Stillat\BladeParser\Nodes\PhpTagNode;
use Stillat\BladeParser\Nodes\VerbatimNode;
use Stillat\BladeParser\Parser\DocumentParser;

class Compiler
{
    use CompilesAuthorizations, CompilesClasses, CompilesComponents,
        CompilesConditionals, CompilesCustomDirectives, CompilesEchos,
        CompilesErrors, CompilesFragments, CompilesHelpers, CompilesIncludes,
        CompilesInjections, CompilesJs, CompilesJson, CompilesLayouts,
        CompilesLoops, CompilesRawPhp, CompilesStacks, CompilesStyles,
        CompilesTranslations, CompilesVerbatim, InteractsWithBladeErrors,
        ManagesCustomConditions, ManagesCustomDirectives, ReflectsClosures;

    private LoopVariablesExtractor $loopExtractor;

    private DocumentParser $parser;

    private RawTransformer $rawTransformer;

    private ComponentTagCompiler $componentTagCompiler;

    private CompilationTarget $compilationTarget = CompilationTarget::TemplateOutput;

    private StringBuffer $compilationBuffer;

    /**
     * @var callable[]
     */
    private array $appendCallbacks = [];

    /**
     * The array of anonymous component namespaces to autoload from.
     */
    protected array $anonymousComponentNamespaces = [];

    /**
     * The anonymous component paths to use.
     */
    protected array $anonymousComponentPaths = [];

    /**
     * The array of class component aliases and their class names.
     */
    protected array $classComponentAliases = [];

    /**
     * The array of class component namespaces to autoload from.
     */
    protected array $classComponentNamespaces = [];

    /**
     * Indicates if component tags should be compiled.
     */
    protected bool $compilesComponentTags = true;

    /**
     * All the registered precompilers.
     */
    protected array $precompilers = [];

    /**
     * The "regular" / legacy echo string format.
     */
    protected string $echoFormat = 'e(%s)';

    /**
     * Footer lines to add to the end of the compiled document.
     */
    protected array $footer = [];

    /**
     * All the registered extensions.
     */
    protected array $extensions = [];

    /**
     * All the registered custom directives.
     */
    protected array $customDirectives = [];

    /**
     * All custom "condition" handlers.
     */
    protected array $conditions = [];

    protected static array $componentHashStack = [];

    /**
     * Indicates if the compiler should fail on parser errors.
     */
    private bool $failOnParserErrors = false;

    /**
     * Indicates if the compiler should fail on any parser errors.
     */
    private bool $failStrictly = false;

    public function __construct(DocumentParser $parser)
    {
        $this->compilationBuffer = new StringBuffer();
        $this->loopExtractor = new LoopVariablesExtractor();
        $this->parser = $parser;
        $this->rawTransformer = new RawTransformer();
        $this->componentTagCompiler = new ComponentTagCompiler(
            new ComponentNodeCompiler(),
            new DocumentParser()
        );
    }

    /**
     * Register a custom component tag compiler.
     *
     * This method will automatically register the provided tag name
     * with the component tag compiler.
     *
     * @param  string  $tagName The custom component tag prefix.
     * @param  CustomComponentTagCompiler  $compiler The compiler instance.
     * @return $this
     */
    public function registerCustomComponentTagCompiler(string $tagName, CustomComponentTagCompiler $compiler): Compiler
    {
        $this->componentTagCompiler->registerCustomCompiler($tagName, $compiler);

        return $this;
    }

    /**
     * Get a new component hash for a component name.
     */
    public static function newComponentHash(string $component): string
    {
        static::$componentHashStack[] = $hash = hash('xxh128', $component);

        return $hash;
    }

    /**
     * Sets whether to compile core Laravel component tags.
     *
     * When set to false, the internal component tag compiler will
     * not compile Laravel component tags (<x-, <x:, etc.).
     *
     * @param  bool  $compileCoreComponents Whether to compile core Laravel component tags.
     * @return $this
     */
    public function setCompileCoreComponents(bool $compileCoreComponents): Compiler
    {
        $this->componentTagCompiler->setCompileCoreComponents($compileCoreComponents);

        return $this;
    }

    /**
     * Compile a class component opening.
     */
    public static function compileClassComponentOpening(string $component, string $alias, string $data, string $hash): string
    {
        return implode("\n", [
            '<?php if (isset($component)) { $__componentOriginal'.$hash.' = $component; } ?>',
            '<?php $component = '.$component.'::resolve('.($data ?: '[]').' + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>',
            '<?php $component->withName('.$alias.'); ?>',
            '<?php if ($component->shouldRender()): ?>',
            '<?php $__env->startComponent($component->resolveView(), $component->data()); ?>',
        ]);
    }

    /**
     * Retrieves a collection of all parser errors.
     */
    public function getErrors(): Collection
    {
        return $this->parser->getErrors();
    }

    /**
     * Adds a callback that will be invoked after the compiler finishes compiling a node.
     *
     * The provided callback will be invoked each time the compiler
     * has finished compiling a node, and it has been appended
     * to the internal output buffer. The callback will receive
     * an instance of `Stillat\BladeParser\Compiler\AppendState`
     * as its first argument.
     *
     * Callbacks are invoked in the order they were registered.
     *
     * @param  callable  $callback The callback.
     * @return $this
     */
    public function onAppend(callable $callback): Compiler
    {
        $this->appendCallbacks[] = $callback;

        return $this;
    }

    /**
     * Register a custom Blade compiler extension.
     *
     * This method has the same behavior as the
     * `Illuminate\View\Compilers\BladeCompiler::extend` method. You do
     * *not* need to manually call this method to sync compiler information
     * if you use the default compiler factory methods/service bindings.
     */
    public function extend(callable $compiler): void
    {
        $this->extensions[] = $compiler;
    }

    /**
     * Get the extensions used by the compiler.
     *
     * Retrieves all extensions registered with the compiler
     * via the `extend` method.
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * Sets whether the compiler should fail on parser errors.
     *
     * When set to true, the compiler will throw an instance of
     * `Stillat\BladeParser\Errors\Exceptions\CompilationException`
     * whenever it encounters a parser error.
     *
     * @param  bool  $failOnParserErrors Whether to fail on parser errors.
     * @return $this
     */
    public function setFailOnParserErrors(bool $failOnParserErrors): Compiler
    {
        $this->failOnParserErrors = $failOnParserErrors;

        return $this;
    }

    /**
     * Returns a value indicating if the compiler will fail on parser errors.
     */
    public function getFailOnParserErrors(): bool
    {
        return $this->failOnParserErrors;
    }

    /**
     * Sets whether the compiler will fail on any parser error.
     *
     * When set to true, the compiler will fail on any error type. When
     * set to false, it will only fail on fatal errors.
     *
     * @param  bool  $isParserErrorsStrict Whether to fail on any parser error.
     * @return $this
     */
    public function setParserErrorsIsStrict(bool $isParserErrorsStrict): Compiler
    {
        $this->failStrictly = $isParserErrorsStrict;

        return $this;
    }

    /**
     * Returns a value indicating if the compiler will fail on any parser error.
     */
    public function getParserErrorsIsStrict(): bool
    {
        return $this->failStrictly;
    }

    /**
     * Sets and overrides all compiler extensions.
     *
     * This method will override any extension that
     * had been previously registered with the `extend` method.
     *
     * In default setups, this is set to the return value of
     * `Illuminate\View\Compilers\BladeCompiler::getExtensions()`
     *
     * @param  array  $extensions The extensions.
     */
    public function setExtensions(array $extensions): Compiler
    {
        $this->extensions = $extensions;

        return $this;
    }

    /**
     * Sets and overrides all anonymous component namespaces.
     *
     * In default setups, this is set to the return value of
     * `Illuminate\View\Compilers\BladeCompiler::getAnonymousComponentNamespaces()`
     *
     * @param  array  $anonymousNamespaces The anonymous namespaces.
     */
    public function setAnonymousComponentNamespaces(array $anonymousNamespaces): Compiler
    {
        $this->anonymousComponentNamespaces = $anonymousNamespaces;
        $this->componentTagCompiler->setAnonymousComponentNamespaces($anonymousNamespaces);

        return $this;
    }

    /**
     * Sets and overrides all class component aliases.
     *
     * In default setups, this is set to the return value of
     * `Illuminate\View\Compilers\BladeCompiler::getClassComponentAliases()`
     *
     * @param  array  $aliases The class component aliases.
     */
    public function setClassComponentAliases(array $aliases): Compiler
    {
        $this->classComponentAliases = $aliases;
        $this->componentTagCompiler->setAliases($aliases);

        return $this;
    }

    /**
     * Sets and overrides all class component namespaces.
     *
     * In default setups, this is set to the return value of
     * `Illuminate\View\Compilers\BladeCompiler::getClassComponentNamespaces()`
     *
     * @param  array  $namespaces The class component namespaces.
     */
    public function setClassComponentNamespaces(array $namespaces): Compiler
    {
        $this->classComponentNamespaces = $namespaces;
        $this->componentTagCompiler->setNamespaces($namespaces);

        return $this;
    }

    /**
     * Sets and overrides all anonymous component paths.
     *
     * In default setups, this is set to the return value of
     * `Illuminate\View\Compilers\BladeCompiler::getAnonymousComponentPaths()`
     *
     * @param  array  $paths The anonymous component paths.
     */
    public function setAnonymousComponentPaths(array $paths): Compiler
    {
        $this->anonymousComponentPaths = $paths;
        $this->componentTagCompiler->setAnonymousComponentPaths($paths);

        return $this;
    }

    /**
     * Returns a reference to the internal ComponentTagCompiler instance.
     */
    public function getComponentTagCompiler(): ComponentTagCompiler
    {
        return $this->componentTagCompiler;
    }

    /**
     * Sets whether the compiler will fail when it encounters unknown component classes.
     *
     * @param  bool  $doThrow Whether to throw on unknown component classes.
     */
    public function setThrowExceptionOnUnknownComponentClass(bool $doThrow): void
    {
        $this->componentTagCompiler->getComponentNodeCompiler()->setThrowExceptionOnUnknownComponentClass($doThrow);
    }

    /**
     * Sets whether to compile class component tags.
     *
     * @param  bool  $compilesComponentTags Whether to compile component tags.
     */
    public function setCompilesComponentTags(bool $compilesComponentTags): Compiler
    {
        $this->compilesComponentTags = $compilesComponentTags;

        return $this;
    }

    /**
     * Indicates if the compiler compiles class component tags.
     */
    public function getCompilesComponentTags(): bool
    {
        return $this->compilesComponentTags;
    }

    /**
     * Sets the internal compilation target.
     *
     * @param  CompilationTarget  $compilationTarget The compilation target.
     */
    public function setCompilationTarget(CompilationTarget $compilationTarget): Compiler
    {
        $this->compilationTarget = $compilationTarget;

        return $this;
    }

    /**
     * Sets and overrides all custom condition handlers.
     *
     * In default setups, this is set to the return value of
     * `Illuminate\View\Compilers\BladeCompiler::$conditions`
     * protected property.
     *
     * @param  array  $conditions The condition handlers.
     */
    public function setConditions(array $conditions): Compiler
    {
        $this->conditions = $conditions;

        return $this;
    }

    /**
     * Gets all custom condition handlers.
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * Registers a precompiler with the compiler.
     *
     * This method has the same behavior as the
     * `Illuminate\View\Compilers\BladeCompiler::precompiler` method. You do
     * *not* need to manually call this method to sync compiler information
     * if you use the default compiler factory methods/service bindings.
     *
     * @param  callable  $precompiler The precompiler.
     */
    public function precompiler(callable $precompiler): void
    {
        $this->precompilers[] = $precompiler;
    }

    /**
     * Set the echo format to be used by the compiler.
     *
     * This method has the same behavior as the
     * `Illuminate\View\Compilers\BladeCompiler::setEchoFormat` method. You do
     * *not* need to manually call this method to sync compiler information
     * if you use the default compiler factory methods/service bindings.
     *
     * @param  string  $format The format to use.
     */
    public function setEchoFormat(string $format): void
    {
        $this->echoFormat = $format;
    }

    /**
     * Set the "echo" format to double encode entities.
     *
     * This method has the same behavior as the
     * `Illuminate\View\Compilers\BladeCompiler::withDoubleEncoding` method. You do
     * *not* need to manually call this method to sync compiler information
     * if you use the default compiler factory methods/service bindings.
     */
    public function withDoubleEncoding(): void
    {
        $this->setEchoFormat('e(%s, true)');
    }

    /**
     * Set the "echo" format to not double encode entities.
     *
     * This method has the same behavior as the
     * `Illuminate\View\Compilers\BladeCompiler::withoutDoubleEncoding` method. You do
     * *not* need to manually call this method to sync compiler information
     * if you use the default compiler factory methods/service bindings.
     */
    public function withoutDoubleEncoding(): void
    {
        $this->setEchoFormat('e(%s, false)');
    }

    /**
     * Returns the configured precompilers.
     */
    public function getPrecompilers(): array
    {
        return $this->precompilers;
    }

    /**
     * Resets the compiler's intermediate state.
     */
    private function resetIntermediateState(): void
    {
        $this->compilationBuffer->clear();
        $this->footer = [];
    }

    /**
     * Resets the compiler's state.
     */
    public function resetState(): void
    {
        $this->resetIntermediateState();
        self::$componentHashStack = [];
    }

    /**
     * Sets the internal precompilers.
     *
     * In default setups, this is set to the value of the
     * `Illuminate\View\Compilers\BladeCompiler::$precompilers`
     * protected property.
     *
     * @param  array  $precompilers The precompilers.
     */
    public function setPrecompilers(array $precompilers): void
    {
        $this->precompilers = $precompilers;
    }

    protected function getDirectiveArgs(DirectiveNode $directive, bool $wrapParenthesis = true): string
    {
        if (! $directive->hasArguments()) {
            if ($wrapParenthesis) {
                return '()';
            }

            return '';
        }

        return $directive->arguments->content;
    }

    protected function getDirectiveArgsInnerContent(DirectiveNode $directive): string
    {
        if (! $directive->hasArguments()) {
            return '';
        }

        return $directive->arguments->innerContent;
    }

    /**
     * Compile the given Blade template contents.
     *
     * @param  string  $template The template.
     *
     * @throws Exception
     * @throws UnsupportedNodeException
     * @throws CompilationException
     */
    public function compileString(string $template): string
    {
        $this->resetIntermediateState();

        $lineEnding = "\n";

        if (Str::contains($template, "\r\n")) {
            $lineEnding = "\r\n";
        }

        if ($this->compilesComponentTags) {
            // If the document does not contain any components
            // we will just receive the original template
            // as the result, so this won't impact anything.
            $template = $this->componentTagCompiler->compile($template);
        }

        $nodes = $this->parser->parse($template);

        // We only want to add the overhead of document
        // transformation and reparsing the document
        // when we have pre-compilers to execute.
        if (count($this->precompilers) > 0) {
            // We will use the raw transformer here to
            // emulate the @__raw_block_0__@ behavior
            // of the original Blade compiler. This
            // is to ensure parity with third-party
            // libraries and Blade UI frameworks.
            $transformed = $this->rawTransformer->transform($nodes);

            foreach ($this->precompilers as $precompiler) {
                $transformed = $precompiler($transformed);
            }

            // Now that our precomilers have finished,
            // we need to put the PHP and Verbatim
            // blocks back into the document.
            $transformed = $this->rawTransformer->reverseTransformation($transformed);

            // We can now get our final nodes to compile.
            $nodes = $this->parser->parse($transformed);
        }

        // If we have extensions, we can run those at this time.
        // It is possible that our precompilers have changed
        // the structure of the document, so we need to
        // start over with the parse and transform.
        if (count($this->extensions) > 0) {
            $transformed = $this->rawTransformer->transform($nodes);
            $newTransform = '';

            if (count($nodes) > 0 && $nodes[0] instanceof PhpTagNode) {
                // We need to use the getNextPhpTag call
                // in order to make sure we do not
                // duplicate the first PHP node.
                $nextPhp = $this->rawTransformer->getNextPhpTag();
                $newTransform = $nextPhp->content;
            }

            // Use the PHP lexer to extract the PHP code from the document.
            // We will use the transformer and the getNextPhpTag method
            // to emulate the @__raw_block_0__@ behavior of the original
            foreach (token_get_all($transformed) as $token) {
                if (is_array($token) && $token[0] === T_INLINE_HTML) {
                    $tokenValue = $this->compileExtensions($token[1]);
                    $nextPhp = $this->rawTransformer->getNextPhpTag();

                    $newTransform .= $tokenValue;

                    // Add the contents of the PHP tags
                    // back into the final document.
                    if ($nextPhp !== null) {
                        $newTransform .= $nextPhp->content;
                    }
                }
            }

            $transformed = $this->rawTransformer->reverseTransformation($newTransform);
            $nodes = $this->parser->parse($transformed);
        }

        if ($this->failOnParserErrors) {
            $error = $this->getFirstFatalError();

            if ($this->failStrictly) {
                $error = $this->getFirstError();
            }

            if ($error !== null) {
                throw CompilationException::fromParserError($error);
            }
        }

        foreach ($nodes as $node) {
            if ($node instanceof CommentNode) {
                continue;
            } elseif ($node instanceof EchoNode) {
                if ($node->type == EchoType::RawEcho) {
                    $this->appendToBuffer($node, $this->compileRawEchoNode($node));
                } else {
                    $this->appendToBuffer($node, $this->compileEchoNode($node));
                }
            } elseif ($node instanceof LiteralNode) {
                $content = $node->unescapedContent;

                if ($this->compilationTarget == CompilationTarget::ComponentParameter) {
                    $content = StringUtilities::escapeSingleQuotes($content);
                }

                $this->appendToBuffer($node, $content);
            } elseif ($node instanceof DirectiveNode) {
                if (array_key_exists($node->content, $this->customDirectives)) {
                    $paramContent = '';

                    if ($node->hasArguments()) {
                        $paramContent = $node->arguments->innerContent;
                    }

                    $this->appendToBuffer($node, $this->callCustomDirective($node->content, $paramContent));

                    continue;
                }

                $compilationMethod = 'compile'.Str::ucfirst(Str::camel($node->content));

                if (method_exists($this, $compilationMethod)) {
                    $this->appendToBuffer($node, $this->{$compilationMethod}($node));
                } else {
                    throw new CompilationException("Unrecognized directive [@{$node->content}]");
                }
            } elseif ($node instanceof PhpTagNode) {
                $this->appendToBuffer($node, $node->content);
            } elseif ($node instanceof PhpBlockNode) {
                $this->appendToBuffer($node, '<?php '.trim($node->innerContent).' ?>');
            } elseif ($node instanceof VerbatimNode) {
                $this->appendToBuffer($node, $node->innerContent);
            } else {
                if ($node instanceof ComponentNode && ! $this->componentTagCompiler->getCompileCoreComponents()) {
                    $this->appendToBuffer($node, $node->content);

                    continue;
                }
                throw new UnsupportedNodeException('Encountered unknown node type ['.get_class($node).']');
            }
        }

        $compiled = (string) $this->compilationBuffer;

        $compiled = str_replace("\n", $lineEnding, $compiled);

        if (count($this->footer) > 0) {
            $compiled = $this->addFooters($compiled);
        }

        if (! empty($this->echoHandlers)) {
            $compiled = $this->addBladeCompilerVariable($compiled);
        }

        return str_replace(
            ['##BEGIN-COMPONENT-CLASS##', '##END-COMPONENT-CLASS##'],
            '',
            $compiled
        );
    }

    private function appendToBuffer(AbstractNode $node, string $value): void
    {
        if (count($this->appendCallbacks) > 0) {
            $curLine = $this->compilationBuffer->currentLine();
            $this->compilationBuffer->append($value);
            $newLine = $this->compilationBuffer->currentLine();

            $appendState = new AppendState($node, $curLine, $newLine, $value);

            foreach ($this->appendCallbacks as $hook) {
                $hook($appendState);
            }

            return;
        }

        $this->compilationBuffer->append($value);
    }

    /**
     * Execute user defined extensions.
     *
     * @param  string  $value The value to compile.
     */
    protected function compileExtensions(string $value): string
    {
        foreach ($this->extensions as $compiler) {
            $value = $compiler($value, $this);
        }

        return $value;
    }

    /**
     * Add the stored footers to the compiled template.
     *
     * @param  string  $result The compiled template.
     */
    protected function addFooters(string $result): string
    {
        return ltrim($result, "\n")
            ."\n".implode("\n", array_reverse($this->footer));
    }

    /**
     * Strip the parentheses from the given expression.
     */
    public function stripParentheses(string $expression): string
    {
        return StringUtilities::unwrapParentheses($expression);
    }
}
