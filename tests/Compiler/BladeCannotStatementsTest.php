<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('cannot statements are compiled', function () {
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

    expect($result)->toBe($expected);
});
