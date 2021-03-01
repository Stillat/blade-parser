<?php

namespace Stillat\BladeParser\Tests;

class BladeEndSectionsTest extends ParserTestCase
{
    public function testEndSectionsAreCompiled()
    {
        $this->assertSame('<?php $__env->stopSection(); ?>', $this->compiler->compileString('@endsection'));
    }
}
