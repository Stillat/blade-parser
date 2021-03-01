<?php

namespace Stillat\BladeParser\Visitors;

use Stillat\BladeParser\Nodes\Node;
use Stillat\BladeParser\Printers\AbstractNodePrinter;

class PrinterNodeVisitor extends AbstractNodeVisitor
{
    /**
     * The registered printers.
     *
     * @var AbstractNodePrinter[]
     */
    protected $printers = [];

    public function onEnter(Node $node)
    {
        foreach ($this->printers as $printer) {
            $printer->printNode($node);
        }
    }

    public function addPrinter(AbstractNodePrinter $printer)
    {
        $this->printers[] = $printer;
    }
}
