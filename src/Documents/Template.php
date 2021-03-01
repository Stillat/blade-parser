<?php

namespace Stillat\BladeParser\Documents;

use Stillat\BladeParser\Nodes\EchoNode;
use Stillat\BladeParser\Nodes\Node;
use Stillat\BladeParser\Nodes\StaticNode;
use Stillat\BladeParser\Printers\AbstractNodePrinter;
use Stillat\BladeParser\Printers\PrinterOptions;
use Stillat\BladeParser\Traversers\TemplateTraverser;
use Stillat\BladeParser\Visitors\PrinterNodeVisitor;

class Template
{
    /**
     * @var Node[]
     */
    protected $referencesNodes = [];

    /**
     * @var Node[]
     */
    protected $nodes = [];

    protected $newLineType = "\n";
    protected $directiveTypeCount = [];
    protected $staticCount = 0;
    protected $echoCount = 0;

    public function __construct($nodes, $referenceNodes, $newLineType)
    {
        $this->nodes = $nodes;
        $this->referencesNodes = $referenceNodes;
        $this->newLineType = $newLineType;

        $this->buildMetaData();
    }

    private function buildMetaData()
    {
        foreach ($this->referencesNodes as $node) {
            if ($node instanceof StaticNode) {
                $this->staticCount += 1;
            } elseif ($node instanceof EchoNode) {
                $this->echoCount += 1;
            } else {
                if (array_key_exists($node->directive, $this->directiveTypeCount) === false) {
                    $this->directiveTypeCount[$node->directive] = 0;
                }

                $this->directiveTypeCount[$node->directive] += 1;
            }
        }
    }

    public function extendsLayout()
    {
        if (array_key_exists('extends', $this->directiveTypeCount)) {
            return $this->directiveTypeCount['extends'] > 0;
        }

        return false;
    }

    /**
     * Returns a new template traverser for the reference nodes.
     *
     * @return TemplateTraverser
     */
    public function getTraverser()
    {
        return new TemplateTraverser($this->referencesNodes);
    }

    private function makeOptionsFromTemplate()
    {
        $printerOptions = new PrinterOptions();
        $printerOptions->setNewLineStyle($this->newLineType);

        return $printerOptions;
    }

    public function withPrinter(AbstractNodePrinter $printer)
    {
        $printer->setPrinterOptions($this->makeOptionsFromTemplate());

        $visitor = new PrinterNodeVisitor();
        $visitor->addPrinter($printer);

        $traverser = $this->getTraverser();
        $traverser->addVisitor($visitor);

        $traverser->traverse();

        return $printer;
    }
}
