<?php

namespace Stillat\BladeParser\Tests;

use PhpParser\Node\Scalar\String_;
use PHPUnit\Framework\TestCase;
use Stillat\BladeParser\Compilers\PhpCompiler;
use Stillat\BladeParser\Parsers\Blade;
use Stillat\BladeParser\Printers\NodeStructurePrinter;
use Stillat\BladeParser\Printers\Php\Printer;

class ParserTestCase extends TestCase
{

    /**
     * The PhpCompiler instance.
     *
     * @var PhpCompiler
     */
    protected $compiler;

    protected function setUp(): void
    {
        $this->compiler = new PhpCompiler();

        parent::setUp();
    }


}
