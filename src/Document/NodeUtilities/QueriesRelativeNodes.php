<?php

namespace Stillat\BladeParser\Document\NodeUtilities;

use Stillat\BladeParser\Document\PartitionResult;
use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\NodeCollection;

trait QueriesRelativeNodes
{
    /**
     * Retrieves all nodes starting on the target line.
     *
     * Only the node's starting position is considered.
     *
     * @param  int  $line  The target line.
     */
    public function findAllNodesStartingOnLine(int $line): NodeCollection
    {
        $nodes = [];

        foreach ($this->getNodes() as $node) {
            if ($node->position->startLine == $line) {
                $nodes[] = $node;
            }
        }

        return new NodeCollection($nodes);
    }

    /**
     * Partitions the nodes based on the list of provided directive names.
     *
     * @internal
     *
     * @param  array|string  $directiveNames  The directive names to break on.
     */
    public function partitionOnDirectives(array|string $directiveNames): PartitionResult
    {
        if (! is_array($directiveNames)) {
            $directiveNames = [$directiveNames];
        }

        $hasFoundFirstPartition = false;
        $partition = [];
        $partitions = [];
        $leadingNodes = [];

        foreach ($this->getNodes() as $check) {
            if ($check instanceof DirectiveNode) {
                if (in_array($check->content, $directiveNames)) {
                    if ($hasFoundFirstPartition) {
                        $partitions[] = new NodeCollection($partition);
                        $partition = [];
                    }
                    $hasFoundFirstPartition = true;
                }
            }

            if (! $hasFoundFirstPartition) {
                $leadingNodes[] = $check;
            } else {
                $partition[] = $check;
            }
        }

        if (count($partition) > 0) {
            $partitions[] = new NodeCollection($partition);
            $partition = [];
        }

        $partitionResult = new PartitionResult(new NodeCollection($leadingNodes));
        $partitionResult->partitions = $partitions;

        return $partitionResult;
    }

    /**
     * Splits the nodes on the provided node.
     *
     * @internal
     *
     * @param  AbstractNode  $node  The node to split on.
     */
    public function splitNodesOn(AbstractNode $node): array
    {
        $regions = [];
        $regionOneNodes = [];
        $regionTwoNodes = [];
        $isRegionTwo = false;

        foreach ($this->getNodes() as $check) {
            if ($check === $node) {
                $isRegionTwo = true;

                continue;
            }

            if ($isRegionTwo) {
                $regionTwoNodes[] = $check;
            } else {
                $regionOneNodes[] = $check;
            }
        }

        $regions[] = new NodeCollection($regionOneNodes);
        $regions[] = new NodeCollection($regionTwoNodes);

        return $regions;
    }

    /**
     * Gets all the nodes before the provided node.
     *
     * @param  AbstractNode  $node  The check node.
     */
    public function getNodesBefore(AbstractNode $node): NodeCollection
    {
        $nodes = [];

        foreach ($this->getNodes() as $check) {
            if ($check === $node) {
                break;
            }
            $nodes[] = $check;
        }

        return new NodeCollection($nodes);
    }

    /**
     * Gets all the nodes after the provided node.
     *
     * @param  AbstractNode  $node  The check node.
     */
    public function getNodesAfter(AbstractNode $node): NodeCollection
    {
        $nodes = [];
        $collect = false;

        foreach ($this->getNodes() as $check) {
            if ($check === $node) {
                $collect = true;

                continue;
            }

            if ($collect) {
                $nodes[] = $check;
            }
        }

        return new NodeCollection($nodes);
    }

    /**
     * Retrieves all nodes between the provided nodes, including the provided nodes.
     *
     * @param  AbstractNode  $a  The start node.
     * @param  AbstractNode  $b  The end node.
     */
    public function getNodesBetweenInclusive(AbstractNode $a, AbstractNode $b): NodeCollection
    {
        $nodes = [];

        foreach ($this->getNodes() as $node) {
            if ($node->index >= $a->index && $node->index <= $b->index) {
                $nodes[] = $node;
            }
        }

        return new NodeCollection($nodes);
    }

    /**
     * Retrieves all parent nodes for the provided node.
     *
     * @param  AbstractNode  $node  The node.
     */
    public function getAllParentNodesForNode(AbstractNode $node): NodeCollection
    {
        $parents = [];

        $parent = $node->getParent();

        while ($parent != null) {
            $parents[] = $parent;

            $parent = $parent->getParent();
        }

        return new NodeCollection($parents);
    }

    /**
     * Tests if the provided node has a parent of the requested type.
     *
     * @param  AbstractNode  $node  The node.
     * @param  string  $type  The type.
     */
    public function getNodeHasParentOfType(AbstractNode $node, string $type): bool
    {
        return $this->getAllParentNodesForNode($node)->hasAnyOfType($type);
    }

    /**
     * Tests if the provided node has a condition-like parent.
     *
     * @param  AbstractNode  $node  The node.
     */
    public function getNodeHasConditionParent(AbstractNode $node): bool
    {
        return $this->getAllParentNodesForNode($node)->first(fn (AbstractNode $node) => $node instanceof DirectiveNode && $node->getCondition() != null) != null;
    }

    /**
     * Tests if the provided node has a `@forelse` directive parent.
     *
     * @param  AbstractNode  $node  The node.
     */
    public function getNodeHasForElseParent(AbstractNode $node): bool
    {
        return $this->getAllParentNodesForNode($node)->first(fn (AbstractNode $node) => $node instanceof DirectiveNode && $node->getForElse() != null) != null;
    }

    /**
     * Tests if the provided node has a `@switch` directive parent.
     *
     * @param  AbstractNode  $node  The node.
     */
    public function getNodeHasSwitchParent(AbstractNode $node): bool
    {
        return $this->getAllParentNodesForNode($node)->first(fn (AbstractNode $node) => $node instanceof DirectiveNode && $node->getSwitchStatement() != null) != null;
    }
}
