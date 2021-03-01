<?php

namespace Stillat\BladeParser\Compilers;

use Stillat\BladeParser\Parsers\Blade;
use Stillat\BladeParser\Printers\Php\Printer;

class PhpCompiler
{

    /**
     * The Blade parser instance.
     *
     * @var Blade
     */
    protected $parser = null;

    /**
     * The PHP Printer instance.
     *
     * @var Printer
     */
    protected $printer = null;

    public function __construct()
    {
        $this->parser = new Blade();
        $this->printer = new Printer();
    }

    public function extend($callback)
    {
        $this->parser->addReplacement($callback);
    }

    public function directive($name, callable $handler)
    {
        $this->parser->directive($name, $handler);

        return null;
    }

    public function getCustomDirectives()
    {
        return $this->parser->getCustomDirectives();
    }

    public function compileString($input)
    {
        $document = $this->parser->parse($input);
        $this->printer->clearContents();
        $this->printer->setCustomDirectiveHandlers($this->parser->getCustomDirectives());

        return $document->withPrinter($this->printer)->getContents();
    }

}