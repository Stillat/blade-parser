<?php

namespace Stillat\BladeParser\Document\Structures\Concerns;

use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\NodeCollection;
use Stillat\BladeParser\Nodes\Structures\ForElse;
use Stillat\BladeParser\Parser\BladeKeywords;

trait ConstructsForElse
{
    protected function constructForElse(array $nodes): void
    {
        for ($i = 0; $i < count($nodes); $i++) {
            $node = $nodes[$i];

            if (! ($node instanceof DirectiveNode && $node->isClosingDirective == false && $this->getSimplifiedStructureName($node) == BladeKeywords::K_ForElse)) {
                continue;
            }

            $forElse = new ForElse();

            $forElse->constructedFrom = $node;
            $node->isStructure = true;
            $node->structure = $forElse;

            $forElseNodes = $node->getDirectChildren();
            // TODO: Account for multiple empty. Grab first, raise internal error.
            $emptyDirective = $forElseNodes->findDirectiveByName('empty');

            $forElse->emptyDirective = $emptyDirective;

            if ($emptyDirective != null) {
                $splitNodes = $node->getNodes()->splitNodesOn($emptyDirective);

                // TODO: What happens if splitNodes != 2?
                if (count($splitNodes) == 2) {
                    /** @var NodeCollection $loopBody */
                    $loopBody = $splitNodes[0];
                    /** @var NodeCollection $emptyBody */
                    $emptyBody = $splitNodes[1];

                    $forElse->loopBody = $loopBody->values()->all();
                    $forElse->emptyBody = $emptyBody->values()->all();
                }
            } else {
                $forElse->loopBody = $node->getNodes()->values()->all();
            }
        }
    }
}
