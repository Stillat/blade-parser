<?php

namespace Stillat\BladeParser\Document\Structures;

use Stillat\BladeParser\Nodes\DirectiveNode;

class ConditionPairStackItem
{
    /**
     * The intended parent node.
     */
    public DirectiveNode $node;

    /**
     * The start position of the parent node.
     */
    public int $index;

    public function __construct(DirectiveNode $node, int $index)
    {
        $this->node = $node;
        $this->index = $index;
    }
}
