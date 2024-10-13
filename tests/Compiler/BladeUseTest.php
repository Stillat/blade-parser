<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('use statements are compiled', function () {
    $string = "Foo @use('SomeNamespace\SomeClass', 'Foo') bar";
    $expected = "Foo <?php use \SomeNamespace\SomeClass as Foo; ?> bar";
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('use statements without as are compiled', function () {
    $string = "Foo @use('SomeNamespace\SomeClass') bar";
    $expected = "Foo <?php use \SomeNamespace\SomeClass; ?> bar";
    expect($this->compiler->compileString($string))->toEqual($expected);
});
