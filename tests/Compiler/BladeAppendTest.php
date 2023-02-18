<?php

namespace Stillat\BladeParser\Tests\Compiler;

use Stillat\BladeParser\Tests\ParserTestCase;

class BladeAppendTest extends ParserTestCase
{
    public function testAppendSectionsAreCompiled()
    {
        $this->assertSame('<?php $__env->appendSection(); ?>', $this->compiler->compileString('@append'));
    }
}
