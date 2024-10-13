<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('selected statements are compiled', function () {
    $template = '<input @selected(name(foo(bar)))/>';
    $expected = "<input <?php if(name(foo(bar))): echo 'selected'; endif; ?>/>";

    expect($this->compiler->compileString($template))->toEqual($expected);
});

test('checked statements are compiled', function () {
    $template = '<input @checked(name(foo(bar)))/>';
    $expected = "<input <?php if(name(foo(bar))): echo 'checked'; endif; ?>/>";

    expect($this->compiler->compileString($template))->toEqual($expected);
});

test('disabled statements are compiled', function () {
    $template = '<button @disabled(name(foo(bar)))>Foo</button>';
    $expected = "<button <?php if(name(foo(bar))): echo 'disabled'; endif; ?>>Foo</button>";

    expect($this->compiler->compileString($template))->toEqual($expected);
});

test('required statements are compiled', function () {
    $template = '<input @required(name(foo(bar)))/>';
    $expected = "<input <?php if(name(foo(bar))): echo 'required'; endif; ?>/>";

    expect($this->compiler->compileString($template))->toEqual($expected);
});
