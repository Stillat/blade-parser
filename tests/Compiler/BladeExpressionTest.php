<?php

namespace Stillat\BladeParser\Tests\Compiler;

use Stillat\BladeParser\Tests\ParserTestCase;

class BladeExpressionTest extends ParserTestCase
{
    public function testExpressionsOnTheSameLine()
    {
        $this->assertSame('<?php echo app(\'translator\')->get(foo(bar(baz(qux(breeze()))))); ?> space () <?php echo app(\'translator\')->get(foo(bar)); ?>', $this->compiler->compileString('@lang(foo(bar(baz(qux(breeze()))))) space () @lang(foo(bar))'));
    }

    public function testExpressionWithinHTML()
    {
        $this->assertSame('<html <?php echo e($foo); ?>>', $this->compiler->compileString('<html {{ $foo }}>'));
        $this->assertSame('<html<?php echo e($foo); ?>>', $this->compiler->compileString('<html{{ $foo }}>'));
        $this->assertSame('<html <?php echo e($foo); ?> <?php echo app(\'translator\')->get(\'foo\'); ?>>', $this->compiler->compileString('<html {{ $foo }} @lang(\'foo\')>'));
    }
}
