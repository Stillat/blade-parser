<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('canany statements are compiled', function () {
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

    expect($result)->toBe($expected);
});
