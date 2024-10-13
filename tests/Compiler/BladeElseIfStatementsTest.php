<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('else if statements are compiled', function () {
    $string = '@if(name(foo(bar)))
breeze
@elseif(boom(breeze))
boom
@endif';
    $expected = '<?php if(name(foo(bar))): ?>
breeze
<?php elseif(boom(breeze)): ?>
boom
<?php endif; ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});
