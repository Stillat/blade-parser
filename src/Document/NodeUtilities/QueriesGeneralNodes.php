<?php

namespace Stillat\BladeParser\Document\NodeUtilities;

use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\EchoNode;
use Stillat\BladeParser\Nodes\LiteralNode;
use Stillat\BladeParser\Nodes\NodeCollection;
use Stillat\BladeParser\Nodes\PhpBlockNode;
use Stillat\BladeParser\Nodes\PhpTagNode;
use Stillat\BladeParser\Nodes\VerbatimNode;

trait QueriesGeneralNodes
{
    use QueriesComments, QueriesComponents,
        QueriesComponents, QueriesGenerics,
        QueriesRelativeNodes, QueriesStructures;

    private bool $hasResolvedStructures = false;

    private ?NodeCollection $literalCollectionCache = null;

    private ?NodeCollection $componentCollectionCache = null;

    private ?NodeCollection $commentCollectionCache = null;

    private ?NodeCollection $directiveCollectionCache = null;

    private ?NodeCollection $phpBlockCollectionCache = null;

    private ?NodeCollection $phpTagCollectionCache = null;

    private ?NodeCollection $verbatimCollectionCache = null;

    private ?NodeCollection $echoNodeCollectionCache = null;

    protected function resetNodeCollectionCache(): void
    {
        $this->componentCollectionCache = null;
        $this->commentCollectionCache = null;
        $this->directiveCollectionCache = null;
        $this->literalCollectionCache = null;
        $this->phpBlockCollectionCache = null;
        $this->phpTagCollectionCache = null;
        $this->verbatimCollectionCache = null;
        $this->echoNodeCollectionCache = null;
    }

    /**
     * Returns all Blade echo nodes in the document.
     *
     * Retrieves all Blade echo statements from the document. Echo nodes
     * are created after parsing the following types of syntax:
     *
     * - Normal echo: `{{ $variable }}`
     * - Triple echo: `{{{ $variable }}}`
     * - Raw Echo: `{!! $variable !!}`
     */
    public function getEchoes(): NodeCollection
    {
        if ($this->echoNodeCollectionCache == null) {
            $this->echoNodeCollectionCache = $this->allOfType(EchoNode::class);
        }

        return $this->echoNodeCollectionCache;
    }

    /**
     * Returns all Blade php/endphp blocks within the document.
     *
     * Retrieves all raw PHP blocks within the source document that
     * were created using Blade's `@php`/`@endphp` directives.
     *
     * Raw `@php` directives that contain arguments will *not* be converted to
     * instances of `PhpBlockNode`, and will instead become instances
     * of `DirectiveNode`.
     */
    public function getPhpBlocks(): NodeCollection
    {
        if ($this->phpBlockCollectionCache == null) {
            $this->phpBlockCollectionCache = $this->allOfType(PhpBlockNode::class);
        }

        return $this->phpBlockCollectionCache;
    }

    /**
     * Returns all PHP tags within the document.
     *
     * PHP tags are created after parsing raw PHP regions within the source template.
     * PHP tags will be created whenever the following types of PHP tags are encountered:
     *
     * - PHP Short Echo: `<?= ?>`
     * - PHP Open: `<?php ?>`
     *
     * If you are looking to retrieve PHP blocks created using Blade's @php @endphp directives
     * you should refer to the `getPhpBlocks` method instead.
     *
     * Note: The PHP short echo tags will be parsed even if short tags have been disabled in the PHP configuration.
     */
    public function getPhpTags(): NodeCollection
    {
        if ($this->phpTagCollectionCache == null) {
            $this->phpTagCollectionCache = $this->allOfType(PhpTagNode::class);
        }

        return $this->phpTagCollectionCache;
    }

    /**
     * Returns all verbatim blocks within the document.
     *
     * Returns all valid `@verbatim`/`@endverbatim` regions within
     * the source document. Unpaired verbatim directives that could
     * not be converted into a valid `VerbatimNode` instance will
     * either become part of the document's literal content (in the case
     * of the `@verbatim` directive), or will become a standalone
     * `DirectiveNode` (in the case of `@endverbatim`).
     */
    public function getVerbatimBlocks(): NodeCollection
    {
        if ($this->verbatimCollectionCache == null) {
            $this->verbatimCollectionCache = $this->allOfType(VerbatimNode::class);
        }

        return $this->verbatimCollectionCache;
    }

    /**
     * Returns all literal content nodes within the document.
     *
     * Returns the source template's content that could not be
     * parsed into a valid Blade construct. If a document contains
     * no Blade code, the parser will return a single instance of `LiteralNode`.
     */
    public function getLiterals(): NodeCollection
    {
        if ($this->literalCollectionCache == null) {
            $this->literalCollectionCache = $this->allOfType(LiteralNode::class);
        }

        return $this->literalCollectionCache;
    }

    /**
     * Returns all directives within the document.
     *
     * Returns all directives from the source document. Some directive pairs,
     * such as the `@php`/`@endphp` and `@verbatim`/`@endverbatim` pairs will
     * not result in an instance of `DirectiveNode`, as they are handled
     * by the parser directly.
     */
    public function getDirectives(): NodeCollection
    {
        if ($this->directiveCollectionCache == null) {
            $this->directiveCollectionCache = $this->allOfType(DirectiveNode::class);
        }

        return $this->directiveCollectionCache;
    }

    /**
     * Tests if the document contains any Blade directives.
     */
    public function hasAnyDirectives(): bool
    {
        return $this->getDirectives()->isNotEmpty();
    }

    /**
     * Attempts to locate the first instance of a directive with the provided name.
     *
     * Returns the first instance of `DirectiveNode` with the provided name. Returns `null`
     * if no directive was found.
     *
     * @param  string  $name The directive name.
     */
    public function findDirectiveByName(string $name): ?DirectiveNode
    {
        return $this->getDirectives()->first(function (DirectiveNode $directiveNode) use ($name) {
            return $directiveNode->content == $name;
        });
    }

    /**
     * Returns all directives with the provided name in the source document.
     *
     * @param  string  $name The directive name to search
     */
    public function findDirectivesByName(string $name): NodeCollection
    {
        return $this->getDirectives()->filter(function (DirectiveNode $directiveNode) use ($name) {
            return $directiveNode->content == $name;
        })->values();
    }

    /**
     * Tests if the document contains a directive with the provided name.
     *
     * @param  string  $name The directive name.
     */
    public function hasDirective(string $name): bool
    {
        return $this->findDirectiveByName($name) != null;
    }
}
