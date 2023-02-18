<?php

namespace Stillat\BladeParser\Document\Structures\Concerns;

use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Nodes\DirectiveNode;

trait ResolvesStructureDocuments
{
    private function resolveStructureDocuments(array $nodes): void
    {
        foreach ($nodes as $node) {
            if (! ($node instanceof DirectiveNode || $node instanceof  ComponentNode)) {
                continue;
            }
            if ($node->isClosedBy == null) {
                continue;
            }

            if (mb_strlen($node->innerDocumentContent) == 0) {
                $node->innerDocumentContent = $this->document->getPairedNodeInnerDocumentText($node);
            }

            if (mb_strlen($node->outerDocumentContent) == 0) {
                $node->outerDocumentContent = $this->document->getPairedNodeOuterDocumentText($node);
            }
        }
    }
}
