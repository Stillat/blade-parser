<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('continue statements are compiled', function () {
    $string = '@for ($i = 0; $i < 10; $i++)
test
@continue
@endfor';
    $expected = '<?php for($i = 0; $i < 10; $i++): ?>
test
<?php continue; ?>
<?php endfor; ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('continue statements with expression are compiled', function () {
    $string = '@for ($i = 0; $i < 10; $i++)
test
@continue(TRUE)
@endfor';
    $expected = '<?php for($i = 0; $i < 10; $i++): ?>
test
<?php if(TRUE) continue; ?>
<?php endfor; ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('continue statements with argument are compiled', function () {
    $string = '@for ($i = 0; $i < 10; $i++)
test
@continue(2)
@endfor';
    $expected = '<?php for($i = 0; $i < 10; $i++): ?>
test
<?php continue 2; ?>
<?php endfor; ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('continue statements with spaced argument are compiled', function () {
    $string = '@for ($i = 0; $i < 10; $i++)
test
@continue( 2 )
@endfor';
    $expected = '<?php for($i = 0; $i < 10; $i++): ?>
test
<?php continue 2; ?>
<?php endfor; ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('continue statements with faulty argument are compiled', function () {
    $string = '@for ($i = 0; $i < 10; $i++)
test
@continue(-2)
@endfor';
    $expected = '<?php for($i = 0; $i < 10; $i++): ?>
test
<?php continue 1; ?>
<?php endfor; ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});
