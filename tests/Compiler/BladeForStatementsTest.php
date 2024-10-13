<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('for statements are compiled', function () {
    $string = '@for ($i = 0; $i < 10; $i++)
test
@endfor';
    $expected = '<?php for($i = 0; $i < 10; $i++): ?>
test
<?php endfor; ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('nested for statements are compiled', function () {
    $string = '@for ($i = 0; $i < 10; $i++)
@for ($j = 0; $j < 20; $j++)
test
@endfor
@endfor';
    $expected = '<?php for($i = 0; $i < 10; $i++): ?>
<?php for($j = 0; $j < 20; $j++): ?>
test
<?php endfor; ?>
<?php endfor; ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});
