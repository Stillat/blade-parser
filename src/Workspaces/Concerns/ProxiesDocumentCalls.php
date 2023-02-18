<?php

namespace Stillat\BladeParser\Workspaces\Concerns;

use Illuminate\Support\Collection;
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Nodes\NodeCollection;
use Stillat\BladeParser\Workspaces\Workspace;

trait ProxiesDocumentCalls
{
    /**
     * Resolves structures on all documents within the workspace.
     */
    public function resolveStructures(): Workspace
    {
        $this->shouldResolveStructures = true;

        return $this->proxyCall(__FUNCTION__);
    }

    /**
     * Returns all directives with the provided name in the workspace.
     *
     * @param  string  $name The directive name to search
     */
    public function findDirectivesByName(string $name): NodeCollection
    {
        return $this->proxyNodeCollectionCall(__FUNCTION__, [$name]);
    }

    /**
     * Retrieves all Blade comments in the workspace.
     */
    public function getComments(): NodeCollection
    {
        return $this->proxyNodeCollectionCall(__FUNCTION__);
    }

    /**
     * Returns a value indicating if the workspace has any Blade comments.
     */
    public function hasAnyComments(): bool
    {
        return $this->proxyTruthyAssertion(__FUNCTION__);
    }

    /**
     * Returns all Blade echo nodes in the workspace.
     *
     * Retrieves all Blade echo statements from the workspace. Echo nodes
     * are created after parsing the following types of syntax:
     *
     * - Normal echo: `{{ $variable }}`
     * - Triple echo: `{{{ $variable }}}`
     * - Raw Echo: `{!! $variable !!}`
     */
    public function getEchoes(): NodeCollection
    {
        return $this->proxyNodeCollectionCall(__FUNCTION__);
    }

    /**
     * Returns all Blade php/endphp blocks within the workspace.
     *
     * Retrieves all raw PHP blocks within the workspace templates that
     * were created using Blade's `@php`/`@endphp` directives.
     *
     * Raw `@php` directives that contain arguments will *not* be converted to
     * instances of `PhpBlockNode`, and will instead become instances
     * of `DirectiveNode`.
     */
    public function getPhpBlocks(): NodeCollection
    {
        return $this->proxyNodeCollectionCall(__FUNCTION__);
    }

    /**
     * Returns all PHP tags within the workspace.
     *
     * PHP tags are created after parsing raw PHP regions within a template.
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
        return $this->proxyNodeCollectionCall(__FUNCTION__);
    }

    /**
     * Returns all verbatim blocks within the workspace.
     *
     * Returns all valid `@verbatim`/`@endverbatim` regions within
     * a source document. Unpaired verbatim directives that could
     * not be converted into a valid `VerbatimNode` instance will
     * either become part of the document's literal content (in the case
     * of the `@verbatim` directive), or will become a standalone
     * `DirectiveNode` (in the case of `@endverbatim`).
     */
    public function getVerbatimBlocks(): NodeCollection
    {
        return $this->proxyNodeCollectionCall(__FUNCTION__);
    }

    /**
     * Returns all literal content nodes within the workspace.
     *
     * Returns the source template's content that could not be
     * parsed into a valid Blade construct. If a document contains
     * no Blade code, the parser will return a single instance of `LiteralNode`.
     */
    public function getLiterals(): NodeCollection
    {
        return $this->proxyNodeCollectionCall(__FUNCTION__);
    }

    /**
     * Returns all directives within the workspace.
     *
     * Returns all directives from a source document. Some directive pairs,
     * such as the `@php`/`@endphp` and `@verbatim`/`@endverbatim` pairs will
     * not result in an instance of `DirectiveNode`, as they are handled
     * by the parser directly.
     */
    public function getDirectives(): NodeCollection
    {
        return $this->proxyNodeCollectionCall(__FUNCTION__);
    }

    /**
     * Tests if the workspace contains any Blade directives.
     */
    public function hasAnyDirectives(): bool
    {
        return $this->proxyTruthyAssertion(__FUNCTION__);
    }

    /**
     * Returns all component tags within workspace document.
     *
     * This method will return *all* component tags within a document,
     * including closing tags and self-closing tags.
     */
    public function getComponents(): NodeCollection
    {
        return $this->proxyNodeCollectionCall(__FUNCTION__);
    }

    /**
     * Returns all self-closing or opening component tags within the workspace.
     */
    public function getOpeningComponentTags(): NodeCollection
    {
        return $this->proxyNodeCollectionCall(__FUNCTION__);
    }

    /**
     * Finds all components with the provided tag name.
     *
     * This method will return *all* component tags that match the
     * provided name, including closing tags.
     *
     * @param  string  $tagName The tag name to filter on.
     */
    public function findComponentsByTagName(string $tagName): NodeCollection
    {
        return $this->proxyNodeCollectionCall(__FUNCTION__, [$tagName]);
    }

    /**
     * Returns a value indicating if the workspace has any component tags.
     */
    public function hasAnyComponents(): bool
    {
        return $this->proxyTruthyAssertion(__FUNCTION__);
    }

    /**
     * Tests if the workspace contains a directive with the provided name.
     *
     * @param  string  $name The directive name.
     */
    public function hasDirective(string $name): bool
    {
        return $this->proxyTruthyAssertion(__FUNCTION__, [$name]);
    }

    /**
     * Finds all nodes of the provided type.
     *
     * @param  string  $type The type to search.
     */
    public function allOfType(string $type): NodeCollection
    {
        return $this->proxyNodeCollectionCall(__FUNCTION__, [$type]);
    }

    /**
     * Finds all nodes that are not of the provided type.
     *
     * @param  string  $type The type to search.
     */
    public function allNotOfType(string $type): NodeCollection
    {
        return $this->proxyNodeCollectionCall(__FUNCTION__, [$type]);
    }

    /**
     * Tests if the workspace contains any node of the provided type.
     *
     * @param  string  $type The desired type.
     */
    public function hasAnyOfType(string $type): bool
    {
        return $this->proxyTruthyAssertion(__FUNCTION__, [$type]);
    }

    /**
     * Returns all the workspace structures.
     *
     * This method automatically performs structural analysis on a document.
     */
    public function getAllStructures(): Collection
    {
        return $this->proxyCollectionCall(__FUNCTION__);
    }

    /**
     * Returns the direct workspace structures.
     *
     * This method automatically performs structural analysis. Only
     * structures that are at the root of a document, without any
     * parent node, will be returned.
     */
    public function getRootStructures(): Collection
    {
        return $this->proxyCollectionCall(__FUNCTION__);
    }

    /**
     * Returns all the workspaces' switch statements.
     *
     * This method automatically performs structural analysis.
     */
    public function getAllSwitchStatements(): Collection
    {
        return $this->proxyCollectionCall(__FUNCTION__);
    }

    /**
     * Returns all the direct switch statements.
     *
     * This method automatically performs structural analysis. Only
     * `@switch` statements that appear at the root of a document,
     * without any parent nodes, will be returned.
     */
    public function getRootSwitchStatements(): Collection
    {
        return $this->proxyCollectionCall(__FUNCTION__);
    }

    /**
     * Returns all the workspace conditions.
     *
     * This method automatically performs structural analysis.
     */
    public function getAllConditions(): Collection
    {
        return $this->proxyCollectionCall(__FUNCTION__);
    }

    /**
     * Returns all the root workspace conditions.
     *
     * This method automatically performs structural analysis. Only structures
     * that appear at the root of the document, without any parent node, will
     * be returned.
     */
    public function getRootConditions(): Collection
    {
        return $this->proxyCollectionCall(__FUNCTION__);
    }

    /**
     * Returns all the workspace for-else blocks.
     *
     * This method automatically performs structural analysis.
     */
    public function getAllForElse(): Collection
    {
        return $this->proxyCollectionCall(__FUNCTION__);
    }

    /**
     * Returns the direct for-else blocks.
     *
     * This method automatically performs structural analysis. Only
     * nodes that appear at the root of a document, without any
     * parent nodes, will be returned.
     */
    public function getRootForElse(): Collection
    {
        return $this->proxyCollectionCall(__FUNCTION__);
    }

    private function proxyTruthyAssertion(string $methodName, array $args = []): bool
    {
        foreach ($this->getDocuments() as $doc) {
            if ($doc->{$methodName}(...$args)) {
                return true;
            }
        }

        return false;
    }

    private function proxyCollectionCall(string $methodName, array $args = []): Collection
    {
        $results = [];

        foreach ($this->getDocuments()->sortBy(fn (Document $doc) => $doc->getFilePath()) as $doc) {
            foreach ($doc->{$methodName}(...$args) as $result) {
                $results[] = $result;
            }
        }

        return collect($results);
    }

    private function proxyNodeCollectionCall(string $methodName, array $args = []): NodeCollection
    {
        $results = [];

        foreach ($this->getDocuments()->sortBy(fn (Document $doc) => $doc->getFilePath()) as $document) {
            foreach ($document->{$methodName}(...$args) as $node) {
                $results[] = $node;
            }
        }

        return new NodeCollection($results);
    }

    private function proxyCall(string $methodName): Workspace
    {
        if (! method_exists(Document::class, $methodName)) {
            return $this;
        }
        $this->getDocuments()->each(fn (Document $doc) => $doc->$methodName());

        return $this;
    }
}
