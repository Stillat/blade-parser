<?php

namespace Stillat\BladeParser\Tests;

class BladeOverwriteSectionTest extends ParserTestCase
{
    public function testOverwriteSectionsAreCompiled()
    {
        $this->assertSame('<?php $__env->stopSection(true); ?>', $this->compiler->compileString('@overwrite'));
    }
}
