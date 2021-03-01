<?php

namespace Stillat\BladeParser\Tests;

class BladeStopSectionTest extends ParserTestCase
{
    public function testStopSectionsAreCompiled()
    {
        $this->assertSame('<?php $__env->stopSection(); ?>', $this->compiler->compileString('@stop'));
    }
}
