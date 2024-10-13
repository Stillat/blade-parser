<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('break statements are compiled', function () {
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

    expect($result)->toBe($expected);
});

test('break statements with expression are compiled', function () {
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

    expect($result)->toBe($expected);
});

test('break statements with arguments are compiled', function () {
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

    expect($this->compiler->compileString($template))->toEqual($expected);
});

test('break statements with spaced arguments are compiled', function () {
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

    expect($this->compiler->compileString($template))->toEqual($expected);
});

test('break statements with faulty arguments are compiled', function () {
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

    expect($this->compiler->compileString($template))->toEqual($expected);
});
