<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('dependencies injected as strings are compiled', function () {
    $string = "Foo @inject('baz', 'SomeNamespace\SomeClass') bar";
    $expected = "Foo <?php \$baz = app('SomeNamespace\SomeClass'); ?> bar";
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('dependencies injected as strings are compiled when injected with double quotes', function () {
    $string = 'Foo @inject("baz", "SomeNamespace\SomeClass") bar';
    $expected = 'Foo <?php $baz = app("SomeNamespace\SomeClass"); ?> bar';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('dependencies are compiled', function () {
    $string = "Foo @inject('baz', SomeNamespace\SomeClass::class) bar";
    $expected = "Foo <?php \$baz = app(SomeNamespace\SomeClass::class); ?> bar";
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('dependencies are compiled with double quotes', function () {
    $string = 'Foo @inject("baz", SomeNamespace\SomeClass::class) bar';
    $expected = "Foo <?php \$baz = app(SomeNamespace\SomeClass::class); ?> bar";
    expect($this->compiler->compileString($string))->toEqual($expected);
});
