<?php

namespace Stillat\BladeParser\Tests;

class BladeCanStatementsTest extends ParserTestCase
{

    public function testCanStatementsAreCompiled()
    {
        $string = '@can (\'update\', [$post])
breeze
@elsecan(\'delete\', [$post])
sneeze
@endcan';
        $expected = '<?php if (app(\\Illuminate\\Contracts\\Auth\\Access\\Gate::class)->check(\'update\', [$post])): ?>
breeze
<?php elseif (app(\\Illuminate\\Contracts\\Auth\\Access\\Gate::class)->check(\'delete\', [$post])): ?>
sneeze
<?php endif; ?>';

        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

}