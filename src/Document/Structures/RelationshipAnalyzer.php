<?php

namespace Stillat\BladeParser\Document\Structures;

use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Nodes\DirectiveNode;

class RelationshipAnalyzer
{
    protected Document $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    public function analyze(): void
    {
        $parentStack = [];

        /** @var DirectiveNode|ComponentNode|null $currentParent */
        $currentParent = null;

        foreach ($this->document->getNodes() as $node) {
            $node->parent = $currentParent;

            if ($currentParent != null) {
                $currentParent->childNodes[] = $node;
            }

            if ($node instanceof DirectiveNode || $node instanceof ComponentNode) {
                if ($node->isClosedBy != null) {
                    if ($currentParent != null) {
                        $parentStack[] = $currentParent;
                    }
                    $currentParent = $node;
                }

                if ($node->isOpenedBy != null) {
                    if ($node->isOpenedBy === $currentParent) {
                        // Remove the closing directive off the child list.
                        array_pop($currentParent->childNodes);
                        $currentParent = null;
                    }

                    if (count($parentStack) > 0) {
                        $currentParent = array_pop($parentStack);
                    }
                }
            }
        }

        $parentStack = null;
    }
}
