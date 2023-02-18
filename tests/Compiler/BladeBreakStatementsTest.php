<?php

namespace Stillat\BladeParser\Tests\Compiler;

use Stillat\BladeParser\Tests\ParserTestCase;

class BladeBreakStatementsTest extends ParserTestCase
{
    public function testBreakStatementsAreCompiled()
    {
        $template = <<<'EOT'
@for ($i = 0; $i < 10; $i++)
test
@break
@endfor
EOT;

        $expected = <<<'EXPECTED'
<?php for($i = 0; $i < 10; $i++): ?>
test
<?php break; ?>
<?php endfor; ?>
EXPECTED;

        $result = $this->compiler->compileString($template);

        $this->assertSame($expected, $result);
    }

    public function testBreakStatementsWithExpressionAreCompiled()
    {
        $template = <<<'EOT'
@for ($i = 0; $i < 10; $i++)
test
@break(TRUE)
@endfor
EOT;

        $expected = <<<'EXPECTED'
<?php for($i = 0; $i < 10; $i++): ?>
test
<?php if(TRUE) break; ?>
<?php endfor; ?>
EXPECTED;

        $result = $this->compiler->compileString($template);

        $this->assertSame($expected, $result);
    }

    public function testBreakStatementsWithArgumentsAreCompiled()
    {
        $template = <<<'EOT'
@for ($i = 0; $i < 10; $i++)
test
@break(2)
@endfor
EOT;

        $expected = <<<'EXPECTED'
<?php for($i = 0; $i < 10; $i++): ?>
test
<?php break 2; ?>
<?php endfor; ?>
EXPECTED;

        $this->assertEquals($expected, $this->compiler->compileString($template));
    }

    public function testBreakStatementsWithSpacedArgumentsAreCompiled()
    {
        $template = <<<'EOT'
@for ($i = 0; $i < 10; $i++)
test
@break( 2 )
@endfor
EOT;

        $expected = <<<'EXPECTED'
<?php for($i = 0; $i < 10; $i++): ?>
test
<?php break 2; ?>
<?php endfor; ?>
EXPECTED;

        $this->assertEquals($expected, $this->compiler->compileString($template));
    }

    public function testBreakStatementsWithFaultyArgumentsAreCompiled()
    {
        $template = <<<'EOT'
@for ($i = 0; $i < 10; $i++)
test
@break(-2)
@endfor
EOT;

        $expected = <<<'EXPECTED'
<?php for($i = 0; $i < 10; $i++): ?>
test
<?php break 1; ?>
<?php endfor; ?>
EXPECTED;

        $this->assertEquals($expected, $this->compiler->compileString($template));
    }
}
