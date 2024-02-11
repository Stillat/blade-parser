<?php

namespace Stillat\BladeParser\Nodes;

class NodeIndexer
{
    /**
     * Resets the indexes on the provided nodes.
     *
     * @param  BaseNode[]  $nodes  The nodes to index.
     */
    public static function indexNodes(array $nodes): void
    {
        $curIndex = 0;

        foreach ($nodes as $node) {
            $node->index = $curIndex;
            $curIndex += 1;
        }
    }
}
