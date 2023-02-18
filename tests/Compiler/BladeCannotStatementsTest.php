<?php

namespace Stillat\BladeParser\Tests\Compiler;

use Stillat\BladeParser\Tests\ParserTestCase;

class BladeCannotStatementsTest extends ParserTestCase
{
    public function testCannotStatementsAreCompiled()
    {
        $template = <<<'EOT'
@cannot ('update', [$post])
breeze
@elsecannot('delete', [$post])
sneeze
@endcannot
EOT;

        $expected = <<<'EXPECTED'
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->denies('update', [$post])): ?>
breeze
<?php elseif (app(\Illuminate\Contracts\Auth\Access\Gate::class)->denies('delete', [$post])): ?>
sneeze
<?php endif; ?>
EXPECTED;

        $result = $this->compiler->compileString($template);

        $this->assertSame($expected, $result);
    }
}
