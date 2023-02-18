<?php

namespace Stillat\BladeParser\Tests\Compiler;

use Stillat\BladeParser\Tests\ParserTestCase;

class BladeYieldTest extends ParserTestCase
{
    public function testYieldsAreCompiled()
    {
        $this->assertSame('<?php echo $__env->yieldContent(\'foo\'); ?>', $this->compiler->compileString('@yield(\'foo\')'));
        $this->assertSame('<?php echo $__env->yieldContent(\'foo\', \'bar\'); ?>', $this->compiler->compileString('@yield(\'foo\', \'bar\')'));
        $this->assertSame('<?php echo $__env->yieldContent(name(foo)); ?>', $this->compiler->compileString('@yield(name(foo))'));
    }
}
