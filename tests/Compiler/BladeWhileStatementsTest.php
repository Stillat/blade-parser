<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('while statements are compiled', function () {
    $string = '@while ($foo)
test
@endwhile';
    $expected = '<?php while($foo): ?>
test
<?php endwhile; ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('nested while statements are compiled', function () {
    $string = '@while ($foo)
@while ($bar)
test
@endwhile
@endwhile';
    $expected = '<?php while($foo): ?>
<?php while($bar): ?>
test
<?php endwhile; ?>
<?php endwhile; ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});
