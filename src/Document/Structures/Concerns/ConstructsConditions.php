<?php

namespace Stillat\BladeParser\Document\Structures\Concerns;

use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\Structures\Condition;
use Stillat\BladeParser\Nodes\Structures\ConditionalBranch;
use Stillat\BladeParser\Parser\BladeKeywords;

trait ConstructsConditions
{
    protected function constructConditions(array $nodes): void
    {
        for ($i = 0; $i < count($nodes); $i++) {
            $node = $nodes[$i];

            if (! ($node instanceof DirectiveNode && $node->isClosingDirective == false && $this->getSimplifiedStructureName($node) == BladeKeywords::K_If)) {
                continue;
            }

            $chainedDirectives = $node->getChainedClosingDirectives();
            $chainedDirectives->pop(); // Removes the closing directive.

            $condition = new Condition();
            $condition->constructedFrom = $node;

            $node->isStructure = true;
            $node->structure = $condition;

            $condition->branches[] = new ConditionalBranch($node);

            foreach ($chainedDirectives as $chainedDirective) {
                $condition->branches[] = new ConditionalBranch($chainedDirective);
            }
        }
    }
}
