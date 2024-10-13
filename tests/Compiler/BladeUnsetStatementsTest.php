<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('unset statements are compiled', function () {
    $string = '@unset ($unset)';
    $expected = '<?php unset($unset); ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});
