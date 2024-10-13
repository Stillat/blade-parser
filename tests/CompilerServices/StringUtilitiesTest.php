<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Compiler\CompilerServices\StringUtilities;

test('wrapping does not wrap dollar variables', function () {
    expect(StringUtilities::wrapInSingleQuotes('$test'))->toBe('$test');
});

test('wrapping single quote strings does not double up quotes', function () {
    $input = "'test'";
    expect(StringUtilities::wrapInSingleQuotes($input))->toBe($input);
});

test('has trailing whitespace empty strings', function () {
    expect(StringUtilities::hasTrailingWhitespace(''))->toBeFalse();
});

test('has leading whitespace empty strings', function () {
    expect(StringUtilities::hasLeadingWhitespace(''))->toBeFalse();
});

test('break by new line', function () {
    $input = <<<'EOT'
One
Two\nThree
Four
EOT;
    expect(StringUtilities::breakByNewLine($input))->toHaveCount(3);
});
