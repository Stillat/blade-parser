<?php

namespace Stillat\BladeParser\Traversers;

use Stillat\BladeParser\Nodes\Node;
use Stillat\BladeParser\Visitors\AbstractNodeVisitor;

class NodeTraverser
{
    /**
     * The registered visitors.
     *
     * @var AbstractNodeVisitor[]
     */
    protected $nodeVisitors = [];

    /**
     * @param Node[] $nodes The nodes.
     */
    public function traverseNodes($nodes)
    {
        foreach ($nodes as $node) {
            foreach ($this->nodeVisitors as $visitor) {
                $visitor->onEnter($node);
            }
        }
    }

    public function addVisitor(AbstractNodeVisitor $visitor)
    {
        $this->nodeVisitors[] = $visitor;
    }
}
