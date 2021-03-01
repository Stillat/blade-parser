<?php

namespace Stillat\BladeParser\Tests;

class BladeIfEmptyStatementsTest extends ParserTestCase
{
    public function testIfStatementsAreCompiled()
    {
        $string = '@empty ($test)
breeze
@endempty';
        $expected = '<?php if(empty($test)): ?>
breeze
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }
}
