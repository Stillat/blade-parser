<?php

namespace Stillat\BladeParser\Document;

use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use Stillat\BladeParser\Compiler\CompilerFactory;
use Stillat\BladeParser\Document\Concerns\ManagesDocumentStructures;
use Stillat\BladeParser\Document\Concerns\ManagesDocumentValidation;
use Stillat\BladeParser\Document\Concerns\ManagesTextExtraction;
use Stillat\BladeParser\Document\NodeUtilities\QueriesGeneralNodes;
use Stillat\BladeParser\Errors\BladeError;
use Stillat\BladeParser\Errors\Exceptions\CompilationException;
use Stillat\BladeParser\Errors\Exceptions\UnsupportedNodeException;
use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Nodes\Concerns\InteractsWithBladeErrors;
use Stillat\BladeParser\Nodes\Fragments\Fragment;
use Stillat\BladeParser\Nodes\LiteralNode;
use Stillat\BladeParser\Nodes\NodeCollection;
use Stillat\BladeParser\Nodes\NodeIndexer;
use Stillat\BladeParser\Parser\DocumentParser;
use Stillat\BladeParser\Parser\DocumentParserFactory;
use Stillat\BladeParser\Parser\HtmlFragments\FragmentPositionsAnalyzer;
use Stillat\BladeParser\Parser\HtmlFragments\FragmentsDocumentParser;
use Stillat\BladeParser\Support\Utf8StringIterator;
use Stillat\BladeParser\Support\Utilities\Paths;
use Stillat\BladeParser\Validation\ValidationResult;

class Document
{
    use Macroable, QueriesGeneralNodes, ManagesDocumentStructures,
        ManagesTextExtraction, InteractsWithBladeErrors,
        ManagesDocumentValidation;

    private ?Utf8StringIterator $docString = null;

    /**
     * @var BladeError[]
     */
    protected array $errors = [];

    /**
     * @var BladeError[]
     */
    protected array $validationErrors = [];

    protected string $nodeText = '';

    /**
     * @var string[]
     */
    protected array $directiveNames = [];

    /**
     * @var AbstractNode[]
     */
    protected array $nodes = [];

    /**
     * The document's file path.
     */
    protected ?string $filePath = null;

    /**
     * @var Fragment[]
     */
    protected array $fragments = [];

    /**
     * Indicates if the document has cached fragments.
     */
    protected bool $hasCachedFragments = false;

    /**
     * Sets the document's file path.
     *
     * Document file paths are optional for most use cases,
     * but can be used by other features such as Workspaces
     * or some validators.
     *
     * @param  string|null  $path The file path.
     */
    public function setFilePath(?string $path): Document
    {
        $this->filePath = Paths::normalizePath($path);

        return $this;
    }

    /**
     * Gets the document's file path.
     */
    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    /**
     * Sets the document's directive names.
     *
     * The directive names supplied to this method should match those
     * that were used when initially parsing the input template. These
     * directive names will be used when performing structural analysis.
     * If you are using the `Blade::fromText($template)` static API,
     * this is managed for you.
     *
     * @param  array  $directives The directive names.
     * @return $this
     */
    public function setDirectiveNames(array $directives): Document
    {
        $this->directiveNames = $directives;

        return $this;
    }

    /**
     * Gets the directive names.
     *
     * @return string[]
     */
    public function getDirectiveNames(): array
    {
        return $this->directiveNames;
    }

    /**
     * Removes the document instance from all attached nodes.
     *
     * Calling this method will remove the document instance from
     * all nodes that currently belong to this document. This
     * can be useful if you simply want lightweight instances
     * and do not necessarily care about the document instance.
     *
     * Additionally, this method may be called internally
     * as a result of removing or modifying the node list.
     */
    public function releaseNodesFromDocument(): void
    {
        foreach ($this->nodes as $node) {
            $node->setDocument(null);
        }
    }

    /**
     * Sets the document nodes.
     *
     * Sets the nodes that represent the parsed template, as well
     * as the original document text. The original document text
     * will be used with other features, like text extraction.
     *
     * @param  AbstractNode[]  $nodes The nodes.
     * @param  string  $nodeText The original document text.
     */
    public function setNodes(array $nodes, string $nodeText): Document
    {
        $this->docString = new Utf8StringIterator($nodeText);

        // Release any existing nodes.
        $this->releaseNodesFromDocument();

        foreach ($nodes as $node) {
            $node->setDocument($this);
        }

        $this->nodeText = $nodeText;
        $this->nodes = $nodes;
        $this->resetNodeCollectionCache();

        return $this;
    }

    /**
     * Sets the Blade errors.
     *
     * This is typically called by other systems such as the validator.
     * Calling this method will *overwrite* any existing `BladeError`
     * instances on the current instance.
     *
     * @param  BladeError[]  $errors The errors.
     */
    public function setErrors(array $errors): Document
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * Returns the Blade errors.
     *
     * Returns a Collection instance containing the document's errors.
     *
     * This method will return errors from all sources, such as
     * parser and validation errors.
     */
    public function getErrors(): Collection
    {
        return collect(array_merge($this->errors, $this->validationErrors));
    }

    /**
     * Adds a validation result instance to the document.
     *
     * Adds a single ValidationResult instance to the document's list
     * of errors. Calling this method will cause the result to be
     * converted to an instance of `BladeError` automatically. If you
     * already have a `BladeError` instance, you should call the
     * `addValidationError(BladeError $error)` method instead.
     *
     * @param  ValidationResult  $result The validation result.
     * @return $this
     */
    public function addValidationResult(ValidationResult $result): Document
    {
        return $this->addValidationError($result->toBladeError());
    }

    /**
     * Adds a validation error instance to the document.
     *
     * Adds a single `BladeError` instance to the document's list
     * of validation errors. Validation errors are stored separately
     * from other errors, and can be retrieved independently.
     *
     * @param  BladeError  $error The Blade error.
     * @return $this
     */
    public function addValidationError(BladeError $error): Document
    {
        $this->validationErrors[] = $error;

        return $this;
    }

    /**
     * Returns a collection containing the document's validation errors.
     *
     * Errors added to the document via. the `addValidationResult` or
     * `addValidationError` methods will be included in the results.
     */
    public function getValidationErrors(): Collection
    {
        return collect($this->validationErrors);
    }

    /**
     * Gets the document nodes.
     *
     * Returns a `NodeCollection` instance containing the nodes that
     * represent the parsed template.
     */
    public function getNodes(): NodeCollection
    {
        return new NodeCollection($this->nodes);
    }

    /**
     * Gets the document nodes as a normal array.
     *
     * Returns a PHP array containing the nodes that represent
     * the parsed template. The nodes returned by this call
     * are the same as those from the `getNodes` method call.
     *
     * @return AbstractNode[]
     */
    public function getNodeArray(): array
    {
        return $this->nodes;
    }

    /**
     * Gets the root document nodes.
     *
     * Returns a `NodeCollection` instance containing all nodes
     * that do *not* have a parent node. Invoking this method
     * will trigger structural analysis.
     */
    public function getRootNodes(): NodeCollection
    {
        $this->resolveStructures();

        return $this->getNodes()->where(function (AbstractNode $node) {
            return $node->parent == null;
        })->values();
    }

    /**
     * Syncs the document details from the parser instance.
     *
     * @internal
     *
     * @param  DocumentParser  $parser The parser instance.
     * @return $this
     */
    public function syncFromParser(DocumentParser $parser): Document
    {
        return $this->setNodes($parser->getNodes(), $parser->getParsedContent())
            ->setDirectiveNames($parser->getDirectiveNames())
            ->setErrors($parser->getErrors()->all());
    }

    /**
     * Constructs a new Document instance from the provided document text.
     *
     * The Document instance returned from this method is created by
     * invoking `DocumentFactory::makeDocument()`.
     *
     * @param  string  $document The template content.
     * @param  string|null  $filePath An optional file path.
     * @param  string[]  $customComponentTags A list of custom component tag names.
     * @param  DocumentOptions|null  $documentOptions Custom document options, if any.
     */
    public static function fromText(string $document, ?string $filePath = null, array $customComponentTags = [], ?DocumentOptions $documentOptions = null): Document
    {
        $parser = DocumentParserFactory::makeDocumentParser();

        if ($documentOptions) {
            if (! $documentOptions->withCoreDirectives) {
                $parser->withoutCoreDirectives();
            }

            if (count($documentOptions->customDirectives) > 0) {
                $parser->setDirectiveNames($documentOptions->customDirectives);
            }
        }

        $parser->registerCustomComponentTags($customComponentTags);
        $parser->parse($document);

        return DocumentFactory::makeDocument()->setFilePath($filePath)->syncFromParser($parser);
    }

    /**
     * Extracts the text from the document.
     *
     * This method will extract the literal, non-Blade text from the
     * document. By default, this method will reverse Blade escape
     * sequences in the produced text. This behavior can be
     * changed by supplying a "falsey" value for the `$unEscaped` parameter.
     *
     * @param  bool  $unEscaped Whether to return unescaped text.
     */
    public function extractText(bool $unEscaped = true): string
    {
        return $this->getLiterals()->map(function (LiteralNode $node) use ($unEscaped) {
            if ($unEscaped) {
                return $node->unescapedContent;
            }

            return $node->content;
        })->implode('');
    }

    public function __toString(): string
    {
        return $this->getNodes()->map(function (AbstractNode $node) {
            return (string) $node;
        })->implode('');
    }

    /**
     * Returns a string representation of the document.
     *
     * This method will traverse every node in the document
     * and call its corresponding `toString()` method. Any
     * modifications made to the document's node will
     * be represented in the results of this method call.
     */
    public function toString(): string
    {
        return (string) $this;
    }

    /**
     * Compiles the current document.
     *
     * Compiles the current document to PHP. The compiler instance
     * used internally is constructed by calling the
     * `CompilerFactory::makeCompiler()` static method.
     *
     *
     * @throws CompilationException
     * @throws UnsupportedNodeException
     */
    public function compile(DocumentCompilerOptions $options = null): string
    {
        $compiler = CompilerFactory::makeCompiler();

        if ($options != null) {
            $compiler->setFailOnParserErrors($options->failOnParserErrors);
            $compiler->setParserErrorsIsStrict($options->failStrictly);
            $compiler->setThrowExceptionOnUnknownComponentClass($options->throwExceptionOnUnknownComponentClass);
            $compiler->setCompileCoreComponents($options->compileCoreComponentTags);

            foreach ($options->appendCallbacks as $callback) {
                $compiler->onAppend($callback);
            }

            foreach ($options->customTagCompilers as $tagName => $tagCompiler) {
                $compiler->registerCustomComponentTagCompiler($tagName, $tagCompiler);
            }
        }

        $compiledResult = $compiler->compileString($this->toString());

        $failedComponents = $compiler->getComponentTagCompiler()->getComponentNodeCompiler()->getFailedComponents();

        if (count($failedComponents) > 0) {
            /** @var ComponentNode $component */
            foreach ($failedComponents as $component) {
                $this->addValidationResult(new ValidationResult($component, "Could not locate component class for [{$component->getTagName()}]"));
            }
            $compiler->getComponentTagCompiler()->getComponentNodeCompiler()->clearFailedComponents();
        }

        return $compiledResult;
    }

    /**
     * Removes the provided node instance from the document.
     *
     * Calling this method will reindex the nodes, but will not
     * trigger structural analysis. If structural analysis has
     * already been performed on the document, calling this
     * method may remove the start or ending node of existing pairs.
     *
     * @param  AbstractNode  $node The node to remove.
     * @return $this
     */
    public function removeNode(AbstractNode $node): Document
    {
        $this->nodes = $this->getNodes()->filter(function (AbstractNode $check) use ($node) {
            return $node !== $check;
        })->values()->all();

        NodeIndexer::indexNodes($this->nodes);

        return $this;
    }

    private function toSkipIndex(): array
    {
        $index = [];

        foreach ($this->nodes as $node) {
            if ($node instanceof LiteralNode || $node->position == null) {
                continue;
            }
            $index[$node->position->startOffset] = $node->position->endOffset;
        }

        return $index;
    }

    /**
     * Retrieves the document's HTML fragments.
     */
    public function getFragments(): Collection
    {
        if ($this->hasCachedFragments) {
            return collect($this->fragments);
        }

        $fragmentsParser = new FragmentsDocumentParser();
        $fragmentsParser->setIgnoreRanges($this->toSkipIndex());

        $this->fragments = $fragmentsParser->parse($this->toString());

        $fragmentPositions = new FragmentPositionsAnalyzer();
        $fragmentPositions->setFragments($this->fragments);

        foreach ($this->nodes as $node) {
            if ($node instanceof LiteralNode || $node->position == null) {
                continue;
            }
            $node->fragmentPosition = $fragmentPositions->getContext($node->position->startOffset);
        }

        NodeIndexer::indexNodes($this->fragments);

        return collect($this->fragments);
    }

    /**
     * Resolves the Document's HTML fragments.
     *
     * @return $this
     */
    public function resolveFragments(): Document
    {
        $this->getFragments();

        return $this;
    }

    /**
     * Returns a new document instance from the current document.
     *
     * This method will take into account any changes made to the nodes
     * that represent the current document. The new document created
     * will use the adjusted document text as its source.
     */
    public function toDocument(): Document
    {
        return Document::fromText((string) $this, $this->filePath);
    }
}
