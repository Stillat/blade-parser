<?php

namespace Stillat\BladeParser\Tests\Compiler;

use Stillat\BladeParser\Tests\ParserTestCase;

class BladeWhileStatementsTest extends ParserTestCase
{
    public function testWhileStatementsAreCompiled()
    {
        $string = '@while ($foo)
test
@endwhile';
        $expected = '<?php while($foo): ?>
test
<?php endwhile; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testNestedWhileStatementsAreCompiled()
    {
        $string = '@while ($foo)
@while ($bar)
test
@endwhile
@endwhile';
        $expected = '<?php while($foo): ?>
<?php while($bar): ?>
test
<?php endwhile; ?>
<?php endwhile; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }
}
