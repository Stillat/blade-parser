<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Compiler\CompilerServices\StringSplitter;

beforeEach(function () {
    $this->splitter = new StringSplitter();
});

test('basic string splitting', function () {
    $input = '($foo as $bar)';
    $result = $this->splitter->split($input);

    $expected = [
        '($foo',
        'as',
        '$bar)',
    ];

    expect($result)->toBe($expected);
});

test('string splitting with nested strings', function () {
    $input = '(explode(",", "foo, bar, baz") as $bar)';
    $result = $this->splitter->split($input);

    $expected = [
        '(explode(",",',
        '"foo, bar, baz")',
        'as',
        '$bar)',
    ];

    expect($result)->toBe($expected);
});

test('string containing astring', function () {
    $input = '"just a string"';
    $result = $this->splitter->split($input);

    $expected = [
        '"just a string"',
    ];

    expect($result)->toBe($expected);
});

test('string ending with astring', function () {
    $input = 'one two three "four"';
    $result = $this->splitter->split($input);

    $expected = [
        'one',
        'two',
        'three',
        '"four"',
    ];

    expect($result)->toBe($expected);
});

test('single quoted strings', function () {
    $input = "one two three 'four'";
    $result = $this->splitter->split($input);

    $expected = [
        'one',
        'two',
        'three',
        "'four'",
    ];

    expect($result)->toBe($expected);
});