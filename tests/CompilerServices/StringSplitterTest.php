<?php

namespace Stillat\BladeParser\Tests\CompilerServices;

use Stillat\BladeParser\Compiler\CompilerServices\StringSplitter;
use Stillat\BladeParser\Tests\ParserTestCase;

class StringSplitterTest extends ParserTestCase
{
    protected StringSplitter $splitter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->splitter = new StringSplitter();
    }

    public function testBasicStringSplitting()
    {
        $input = '($foo as $bar)';
        $result = $this->splitter->split($input);

        $expected = [
            '($foo',
            'as',
            '$bar)',
        ];

        $this->assertSame($expected, $result);
    }

    public function testStringSplittingWithNestedStrings()
    {
        $input = '(explode(",", "foo, bar, baz") as $bar)';
        $result = $this->splitter->split($input);

        $expected = [
            '(explode(",",',
            '"foo, bar, baz")',
            'as',
            '$bar)',
        ];

        $this->assertSame($expected, $result);
    }

    public function testStringContainingAString()
    {
        $input = '"just a string"';
        $result = $this->splitter->split($input);

        $expected = [
            '"just a string"',
        ];

        $this->assertSame($expected, $result);
    }

    public function testStringEndingWithAString()
    {
        $input = 'one two three "four"';
        $result = $this->splitter->split($input);

        $expected = [
            'one',
            'two',
            'three',
            '"four"',
        ];

        $this->assertSame($expected, $result);
    }

    public function testSingleQuotedStrings()
    {
        $input = "one two three 'four'";
        $result = $this->splitter->split($input);

        $expected = [
            'one',
            'two',
            'three',
            "'four'",
        ];

        $this->assertSame($expected, $result);
    }
}
