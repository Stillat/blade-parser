<?php

namespace Stillat\BladeParser\Tests\Compiler;

use Stillat\BladeParser\Tests\ParserTestCase;

class BladeCananyStatementsTest extends ParserTestCase
{
    public function testCananyStatementsAreCompiled()
    {
        $template = <<<'EOT'
@canany (['create', 'update'], [$post])
breeze
@elsecanany(['delete', 'approve'], [$post])
sneeze
@endcan
EOT;

        $expected = <<<'EXPECTED'
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['create', 'update'], [$post])): ?>
breeze
<?php elseif (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['delete', 'approve'], [$post])): ?>
sneeze
<?php endif; ?>
EXPECTED;

        $result = $this->compiler->compileString($template);

        $this->assertSame($expected, $result);
    }
}
