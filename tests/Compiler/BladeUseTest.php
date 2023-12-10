<?php

namespace Stillat\BladeParser\Tests\Compiler;

use Stillat\BladeParser\Tests\ParserTestCase;

class BladeUseTest extends ParserTestCase
{
    public function testUseStatementsAreCompiled()
    {
        $string = "Foo @use('SomeNamespace\SomeClass', 'Foo') bar";
        $expected = "Foo <?php use \SomeNamespace\SomeClass as Foo; ?> bar";
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testUseStatementsWithoutAsAreCompiled()
    {
        $string = "Foo @use('SomeNamespace\SomeClass') bar";
        $expected = "Foo <?php use \SomeNamespace\SomeClass; ?> bar";
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }
}
