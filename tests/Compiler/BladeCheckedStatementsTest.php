<?php

namespace Stillat\BladeParser\Tests\Compiler;

use Stillat\BladeParser\Tests\ParserTestCase;

class BladeCheckedStatementsTest extends ParserTestCase
{
    public function testSelectedStatementsAreCompiled()
    {
        $template = '<input @selected(name(foo(bar)))/>';
        $expected = "<input <?php if(name(foo(bar))): echo 'selected'; endif; ?>/>";

        $this->assertEquals($expected, $this->compiler->compileString($template));
    }

    public function testCheckedStatementsAreCompiled()
    {
        $template = '<input @checked(name(foo(bar)))/>';
        $expected = "<input <?php if(name(foo(bar))): echo 'checked'; endif; ?>/>";

        $this->assertEquals($expected, $this->compiler->compileString($template));
    }

    public function testDisabledStatementsAreCompiled()
    {
        $template = '<button @disabled(name(foo(bar)))>Foo</button>';
        $expected = "<button <?php if(name(foo(bar))): echo 'disabled'; endif; ?>>Foo</button>";

        $this->assertEquals($expected, $this->compiler->compileString($template));
    }

    public function testRequiredStatementsAreCompiled()
    {
        $template = '<input @required(name(foo(bar)))/>';
        $expected = "<input <?php if(name(foo(bar))): echo 'required'; endif; ?>/>";

        $this->assertEquals($expected, $this->compiler->compileString($template));
    }
}
