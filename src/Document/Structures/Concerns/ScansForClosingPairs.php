<?php

namespace Stillat\BladeParser\Document\Structures\Concerns;

use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\DirectiveNode;

trait ScansForClosingPairs
{
    /**
     * @param  AbstractNode[]  $nodes
     */
    private function findClosingPair(array $nodes, DirectiveNode $node, array $scanFor): void
    {
        $nodeLength = count($nodes);
        $refStack = 0;

        for ($i = 0; $i < $nodeLength; $i++) {
            $candidateNode = $nodes[$i];

            if ($candidateNode->index <= $node->index) {
                continue;
            }
            if (! $candidateNode instanceof DirectiveNode) {
                continue;
            }

            if ($candidateNode->isClosingDirective && $candidateNode->isOpenedBy != null) {
                continue;
            }
            if (! $candidateNode->isClosingDirective && $candidateNode->isClosedBy != null) {
                continue;
            }

            $directiveName = $this->getSimplifiedStructureName($candidateNode);

            if (! $candidateNode->isClosingDirective && in_array($directiveName, $scanFor)) {
                $refStack += 1;

                continue;
            }

            if (in_array($directiveName, $scanFor) && $refStack > 0) {
                $refStack -= 1;

                continue;
            }

            if ($refStack == 0 && in_array($directiveName, $scanFor)) {
                $candidateNode->isOpenedBy = $node;
                $node->isClosedBy = $candidateNode;
                break;
            }
        }
    }
}
