<?php

namespace Stillat\BladeParser\Printers;

use Stillat\BladeParser\Nodes\Node;

abstract class AbstractNodePrinter
{
    /**
     * The PrinterOptions instance, if any.
     * @var PrinterOptions
     */
    protected $options;

    public function setPrinterOptions(PrinterOptions $options)
    {
        $this->options = $options;
    }

    abstract public function printNode(Node $node);

    abstract public function clearContents();

    abstract public function getContents();

    public function getNewLineStyle()
    {
        if ($this->options === null) {
            return "\n";
        }

        return $this->options->getNewLineStyle();
    }

    protected function adjustNewLines($content)
    {
        return str_replace("\n", $this->getNewLineStyle(), $content);
    }

    protected function trimQuotes($input)
    {
        $input = ltrim($input, '"\'');
        $input = rtrim($input, '"\'');

        return $input;
    }

    protected function wrapInDoubleQuotes($input)
    {
        return '"'.$input.'"';
    }
}
