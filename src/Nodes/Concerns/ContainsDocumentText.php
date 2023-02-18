<?php

namespace Stillat\BladeParser\Nodes\Concerns;

use Stillat\BladeParser\Document\Document;

trait ContainsDocumentText
{
    /**
     * The node's inner document content.
     */
    public string $innerDocumentContent = '';

    /**
     * The node's outer document content.
     */
    public string $outerDocumentContent = '';

    /**
     * Gets the node's inner document content.
     */
    public function getInnerDocumentContent(): string
    {
        return $this->innerDocumentContent;
    }

    /**
     * Gets the node's outer document content.
     */
    public function getOuterDocumentContent(): string
    {
        return $this->outerDocumentContent;
    }

    /**
     * Returns a Document instance from the node's outer document content.
     */
    public function toDocument(): Document
    {
        return Document::fromText($this->getOuterDocumentContent());
    }
}
