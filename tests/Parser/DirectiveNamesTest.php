<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('directives with leading underscores are parsed', function () {
    $this->registerDirective('_test');
    $nodes = $this->parseNodes('@_test');

    expect($nodes)->toHaveCount(1);
    $this->assertDirectiveName($nodes[0], '_test');

    $this->registerDirective('___test');
    $nodes = $this->parseNodes('@___test');

    expect($nodes)->toHaveCount(1);
    $this->assertDirectiveName($nodes[0], '___test');
});

test('directives containing underscores are parsed', function () {
    $this->registerDirective('_directive_test');
    $nodes = $this->parseNodes('@_directive_test');

    expect($nodes)->toHaveCount(1);
    $this->assertDirectiveName($nodes[0], '_directive_test');
});

test('directives with trailing underscore', function () {
    $this->registerDirective('test_');
    $nodes = $this->parseNodes('@test_');

    expect($nodes)->toHaveCount(1);
    $this->assertDirectiveName($nodes[0], 'test_');
});

test('directive names with double colons', function () {
    $this->registerDirective('test::directive');
    $nodes = $this->parseNodes('@test::directive');

    expect($nodes)->toHaveCount(1);
    $this->assertDirectiveName($nodes[0], 'test::directive');
});

test('camel cased directive names', function () {
    $this->registerDirective('testDirective');
    $nodes = $this->parseNodes('@testDirective');

    expect($nodes)->toHaveCount(1);
    $this->assertDirectiveName($nodes[0], 'testDirective');
});
