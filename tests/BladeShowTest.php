<?php

namespace Stillat\BladeParser\Tests;

class BladeShowTest extends ParserTestCase
{
    public function testShowsAreCompiled()
    {
        $this->assertSame('<?php echo $__env->yieldSection(); ?>', $this->compiler->compileString('@show'));
    }
}