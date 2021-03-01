<?php

namespace Stillat\BladeParser\Printers;

use Stillat\BladeParser\Nodes\Node;

class NodeStructurePrinter extends AbstractNodePrinter
{

    protected $buffer = '';

    public function printNode(Node $node)
    {
        $this->buffer .= '{'.$node->getType().'}';
    }


    public function getContents()
    {
        return $this->adjustNewLines($this->buffer);
    }

    public function clearContents()
    {
        $this->buffer = '';
    }
}
