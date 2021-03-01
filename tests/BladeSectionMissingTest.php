<?php

namespace Stillat\BladeParser\Tests;

class BladeSectionMissingTest extends ParserTestCase
{
    public function testSectionMissingStatementsAreCompiled()
    {
        $string = '@sectionMissing("section")
breeze
@endif';
        $expected = '<?php if (empty(trim($__env->yieldContent("section")))): ?>
breeze
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

}