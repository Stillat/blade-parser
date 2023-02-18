<?php

namespace Stillat\BladeParser\Document\Structures\Concerns;

use Stillat\BladeParser\Document\Structures\ConditionPairStackItem;
use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Parser\BladeKeywords;

trait PairsConditionalStructures
{
    /**
     * @param  AbstractNode[]  $nodes
     */
    private function pairConditions(array $nodes): void
    {
        for ($i = 0; $i < count($nodes); $i++) {
            $node = $nodes[$i];

            if ($node instanceof DirectiveNode && $this->isConditionalStructure($node)) {
                $node->conditionStructureName = $this->getSimplifiedStructureName($node);
                $node->isConditionDirective = true;

                if ($this->conditionRequiresClose($node)) {
                    $node->conditionRequiresClose = true;
                    $this->findClosestStructurePair($nodes, $node, $i + 1);
                }
            }
        }
    }

    private function findClosestStructurePair(array $nodes, DirectiveNode $parent, int $startIndex): void
    {
        /** @var ConditionPairStackItem[] $stack */
        $stack = [];
        $nodeLen = count($nodes);
        $conditionCloseIndex = [];

        $initialItem = new ConditionPairStackItem($parent, $startIndex);
        $stack[] = $initialItem;

        // Build a close index.
        for ($i = 0; $i < $nodeLen; $i++) {
            $node = $nodes[$i];

            if ($node instanceof DirectiveNode) {
                $name = mb_strtolower($this->getSimplifiedStructureName($node));

                if ($node->isClosingDirective && $name == BladeKeywords::K_If) {
                    $conditionCloseIndex[$i] = $i;

                    continue;
                } else {
                    if ($name == BladeKeywords::K_ElseIf || $name == BladeKeywords::K_ELSE) {
                        $conditionCloseIndex[$i] = $i;

                        continue;
                    }
                }
            }
        }

        $hasReachedEnd = false;

        while (count($stack) > 0) {
            $curItem = array_pop($stack);

            if ($curItem == null) {
                continue;
            }

            if (array_key_exists($curItem->node->id, $this->abandonedConditionNodes)) {
                break;
            }

            $curNode = $curItem->node;

            if ($hasReachedEnd) {
                break;
            }

            $curName = $this->getSimplifiedStructureName($curNode);
            $curIndex = $curItem->index;
            $doSkipValidation = false;
            $thisValidPairs = $this->getConditionValidClosingPairs($curName);

            for ($i = $curIndex; $i < $nodeLen; $i++) {
                $subNode = $nodes[$i];
                if ($subNode instanceof DirectiveNode) {
                    if ($this->isConditionalStructure($subNode)) {
                        if ($this->conditionRequiresClose($subNode)) {
                            if ($i == $nodeLen - 1) {
                                $hasReachedEnd = true;
                                $this->abandonedConditionNodes[$parent->id] = true;
                                $this->abandonedConditionNodes[$curItem->node->id] = true;
                                break;
                            }

                            $stack[] = $curItem;
                            $newStackItem = new ConditionPairStackItem($subNode, $i + 1);
                            $stack[] = $newStackItem;

                            $doSkipValidation = true;
                            break;
                        }

                        if ($curNode->isClosedBy != null) {
                            continue;
                        }

                        $subNodeName = $this->getSimplifiedStructureName($subNode);
                        $canClose = false;

                        if ($this->getReferenceCount($subNode) == 0 && (($subNode->isClosingDirective && $subNodeName == BladeKeywords::K_If) || in_array($subNodeName, $thisValidPairs))) {
                            $canClose = true;
                        }

                        if (! $subNode->isClosingDirective && $subNodeName == BladeKeywords::K_If) {
                            $canClose = false;
                        }

                        if ($subNode->id == $curNode->id) {
                            $canClose = false;
                        }

                        if ($canClose) {
                            unset($conditionCloseIndex[$i]);
                            $curNode->isClosedBy = $subNode;
                            $subNode->isOpenedBy = $curNode;
                            $this->incrementReferenceCount($subNode);
                            $doSkipValidation = true;
                            break;
                        } else {
                            if ($i == $nodeLen - 1) {
                                $hasReachedEnd = true;
                            }
                        }
                    }
                } else {
                    if ($i == $nodeLen - 1) {
                        $this->abandonedConditionNodes[$parent->id] = true;
                        $this->abandonedConditionNodes[$curItem->node->id] = true;
                        break;
                    }
                }
            }

            if (! $doSkipValidation) {
                if ($parent->isClosedBy === null) {
                    $this->abandonedConditionNodes[$parent->id] = true;
                }
            }
        }
    }
}
