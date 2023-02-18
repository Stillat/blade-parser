<?php

namespace Stillat\BladeParser\Document;

use Stillat\BladeParser\Nodes\NodeCollection;

class PartitionResult
{
    public NodeCollection $leadingNodes;

    /**
     * @var NodeCollection[]
     */
    public array $partitions = [];

    public function __construct(NodeCollection $leadingNodes)
    {
        $this->leadingNodes = $leadingNodes;
    }

    /**
     * Returns a value indicating if the partition contains leading nodes.
     */
    public function hasLeadingNodes(): bool
    {
        return $this->leadingNodes->count() > 0;
    }

    /**
     * Tests if the result contains any partitions.
     */
    public function hasPartitions(): bool
    {
        return count($this->partitions) > 0;
    }
}
