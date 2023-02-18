<?php

namespace Stillat\BladeParser\Document\Structures\Concerns;

use Stillat\BladeParser\Nodes\Components\ComponentNode;

trait PairsComponentTags
{
    private function pairComponentTags(array $nodes): void
    {
        for ($i = 0; $i < count($nodes); $i++) {
            $node = $nodes[$i];

            if (! $node instanceof ComponentNode) {
                continue;
            }
            if ($node->isSelfClosing || $node->isClosingTag) {
                continue;
            }

            $closingComponent = $this->findClosingComponentTag($i + 1, $node->getCompareName(), $nodes);

            if ($closingComponent != null) {
                $node->isClosedBy = $closingComponent;
                $closingComponent->isOpenedBy = $node;
            }
        }
    }

    private function findClosingComponentTag(int $startIndex, string $tagName, array $nodes): ?ComponentNode
    {
        $stackCount = 1;

        for ($i = $startIndex; $i < count($nodes); $i++) {
            $node = $nodes[$i];

            if (! $node instanceof ComponentNode) {
                continue;
            }
            if ($node->isSelfClosing) {
                continue;
            }
            if ($node->isOpenedBy != null) {
                continue;
            }
            if ($node->getCompareName() != $tagName) {
                continue;
            }

            if (! $node->isClosingTag) {
                $stackCount += 1;
            } else {
                $stackCount -= 1;
            }

            if ($stackCount == 0) {
                return $node;
            }
        }

        return null;
    }
}
