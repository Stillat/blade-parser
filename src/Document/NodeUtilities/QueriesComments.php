<?php

namespace Stillat\BladeParser\Document\NodeUtilities;

use Stillat\BladeParser\Nodes\CommentNode;
use Stillat\BladeParser\Nodes\NodeCollection;

trait QueriesComments
{
    /**
     * Retrieves all Blade comments from the document.
     */
    public function getComments(): NodeCollection
    {
        if ($this->commentCollectionCache == null) {
            $this->commentCollectionCache = $this->allOfType(CommentNode::class);
        }

        return $this->commentCollectionCache;
    }

    /**
     * Returns a value indicating if the document has any Blade comments.
     */
    public function hasAnyComments(): bool
    {
        return $this->getComments()->isNotEmpty();
    }
}
