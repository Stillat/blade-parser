<?php

namespace Stillat\BladeParser\Traversers;

use Stillat\BladeParser\Nodes\Node;

class TemplateTraverser extends NodeTraverser
{
    protected $nodes = [];

    /**
     * TemplateTraverser constructor.
     *
     * @param  Node[]  $nodes  The nodes.
     */
    public function __construct($nodes)
    {
        $this->nodes = $nodes;
    }

    public function traverse()
    {
        $this->traverseNodes($this->nodes);
    }
}
