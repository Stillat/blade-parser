<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('statement is compiled without any options', function () {
    $string = '<div x-data="@js($data)"></div>';
    $expected = '<div x-data="<?php echo \Illuminate\Support\Js::from($data)->toHtml() ?>"></div>';

    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('json flags can be set', function () {
    $string = '<div x-data="@js($data, JSON_FORCE_OBJECT)"></div>';
    $expected = '<div x-data="<?php echo \Illuminate\Support\Js::from($data, JSON_FORCE_OBJECT)->toHtml() ?>"></div>';

    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('encoding depth can be set', function () {
    $string = '<div x-data="@js($data, JSON_FORCE_OBJECT, 256)"></div>';
    $expected = '<div x-data="<?php echo \Illuminate\Support\Js::from($data, JSON_FORCE_OBJECT, 256)->toHtml() ?>"></div>';

    expect($this->compiler->compileString($string))->toEqual($expected);
});
