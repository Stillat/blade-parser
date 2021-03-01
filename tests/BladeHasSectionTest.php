<?php

namespace Stillat\BladeParser\Tests;

class BladeHasSectionTest extends ParserTestCase
{
    public function testHasSectionStatementsAreCompiled()
    {
        $string = '@hasSection("section")
breeze
@endif';
        $expected = '<?php if (! empty(trim($__env->yieldContent("section")))): ?>
breeze
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }
}