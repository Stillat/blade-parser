<?php

namespace Stillat\BladeParser\Tests\Compiler;

use Stillat\BladeParser\Tests\ParserTestCase;

class BladeCommentsTest extends ParserTestCase
{
    public function testCommentsAreCompiled()
    {
        $template = '{{--this is a comment--}}';
        $this->assertEmpty($this->compiler->compileString($template));

        $template = '{{--
this is a comment
--}}';
        $this->assertEmpty($this->compiler->compileString($template));

        $template = sprintf('{{-- this is an %s long comment --}}', str_repeat('extremely ', 1000));
        $this->assertEmpty($this->compiler->compileString($template));
    }

    public function testBladeCodeInsideCommentsIsNotCompiled()
    {
        $template = '{{-- @foreach() --}}';

        $this->assertEmpty($this->compiler->compileString($template));
    }
}
