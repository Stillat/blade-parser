<?php

namespace Stillat\BladeParser\Document\NodeUtilities;

use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Nodes\NodeCollection;

trait QueriesComponents
{
    /**
     * Returns all component tags within the document.
     *
     * This method will return *all* component tags within the document,
     * including closing tags and self-closing tags.
     */
    public function getComponents(): NodeCollection
    {
        if ($this->componentCollectionCache == null) {
            $this->componentCollectionCache = $this->allOfType(ComponentNode::class);
        }

        return $this->componentCollectionCache;
    }

    /**
     * Returns all self-closing or opening component tags.
     */
    public function getOpeningComponentTags(): NodeCollection
    {
        return $this->getComponents()->filter(function (ComponentNode $node) {
            return $node->isSelfClosing || ! $node->isClosingTag;
        })->values();
    }

    /**
     * Returns the first component tag within the document with the provided name.
     *
     * @param  string  $tagName The tag name to filter on.
     */
    public function findComponentByTagName(string $tagName): ?ComponentNode
    {
        return $this->findComponentsByTagName($tagName)->first();
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
        return $this->getComponents()->filter(function (ComponentNode $component) use ($tagName) {
            return $component->tagName == $tagName;
        })->values();
    }

    /**
     * Returns a value indicating if the document has any component tags.
     */
    public function hasAnyComponents(): bool
    {
        return $this->getComponents()->isNotEmpty();
    }
}
