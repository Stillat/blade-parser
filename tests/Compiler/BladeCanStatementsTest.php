<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('can statements are compiled', function () {
    $template = <<<'EOT'
@can ('update', [$post])
breeze
@elsecan('delete', [$post])
sneeze
@endcan
EOT;

    $expected = <<<'EXPECTED'
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', [$post])): ?>
breeze
<?php elseif (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', [$post])): ?>
sneeze
<?php endif; ?>
EXPECTED;

    $result = $this->compiler->compileString($template);

    expect($result)->toBe($expected);
});
