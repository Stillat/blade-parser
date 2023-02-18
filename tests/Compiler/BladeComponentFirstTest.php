<?php

namespace Stillat\BladeParser\Tests\Compiler;

use Stillat\BladeParser\Tests\ParserTestCase;

class BladeComponentFirstTest extends ParserTestCase
{
    public function testComponentFirstsAreCompiled()
    {
        $this->assertSame('<?php $__env->startComponentFirst(["one", "two"]); ?>', $this->compiler->compileString('@componentFirst(["one", "two"])'));
        $this->assertSame('<?php $__env->startComponentFirst(["one", "two"], ["foo" => "bar"]); ?>', $this->compiler->compileString('@componentFirst(["one", "two"], ["foo" => "bar"])'));
    }
}
