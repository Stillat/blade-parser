<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('comments are compiled', function () {
    $template = '{{--this is a comment--}}';
    expect($this->compiler->compileString($template))->toBeEmpty();

    $template = '{{--
this is a comment
--}}';
    expect($this->compiler->compileString($template))->toBeEmpty();

    $template = sprintf('{{-- this is an %s long comment --}}', str_repeat('extremely ', 1000));
    expect($this->compiler->compileString($template))->toBeEmpty();
});

test('blade code inside comments is not compiled', function () {
    $template = '{{-- @foreach() --}}';

    expect($this->compiler->compileString($template))->toBeEmpty();
});
