<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Compiler\CompilerServices\StringUtilities;

test('basic string unwrapping', function ($input, $expected) {
    expect(StringUtilities::unwrapParentheses($input))->toBe($expected);
})->with('stringUnwrapDataProvider');

dataset('stringUnwrapDataProvider', function () {
    return [
        ['((()))', ''],
        ['(', '('],
        [')', ')'],
        ['(()', '('],
        ['((((Foo)))', '(Foo'],
        ['Foo)))))', 'Foo)))))'],
        ['(((((((Foo)))))))', 'Foo'],
    ];
});
