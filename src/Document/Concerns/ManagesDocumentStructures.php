<?php

namespace Stillat\BladeParser\Document\Concerns;

use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Document\Structures\StructurePairAnalyzer;

trait ManagesDocumentStructures
{
    /**
     * Resolves structures within the document, such as directive pairs.
     *
     * Structural analysis can only be performed once on a Document instance.
     * If changes have been made to the node structure, you are encouraged
     * to call the `toDocument()` method to construct a new document.
     */
    public function resolveStructures(): Document
    {
        if ($this->hasResolvedStructures) {
            return $this;
        }

        $pairAnalyzer = new StructurePairAnalyzer($this);
        $pairAnalyzer->associate();
        $pairAnalyzer = null;

        $this->hasResolvedStructures = true;

        return $this;
    }
}
