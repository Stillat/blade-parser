<?php

namespace Stillat\BladeParser\Tests;

class BladeHelpersTest extends ParserTestCase
{
    public function testEchosAreCompiled()
    {
        $this->assertSame('<?php echo csrf_field(); ?>', $this->compiler->compileString('@csrf'));
        $this->assertSame('<?php echo method_field(\'patch\'); ?>', $this->compiler->compileString("@method('patch')"));
        $this->assertSame('<?php dd($var1); ?>', $this->compiler->compileString('@dd($var1)'));
        $this->assertSame('<?php dd($var1, $var2); ?>', $this->compiler->compileString('@dd($var1, $var2)'));
        $this->assertSame('<?php dump($var1, $var2); ?>', $this->compiler->compileString('@dump($var1, $var2)'));
    }
}