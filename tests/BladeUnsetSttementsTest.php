<?php

namespace Stillat\BladeParser\Tests;

class BladeUnsetSttementsTest extends ParserTestCase
{
    public function testUnsetStatementsAreCompiled()
    {
        $string = '@unset ($unset)';
        $expected = '<?php unset($unset); ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }
}
