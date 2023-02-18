<?php

namespace Stillat\BladeParser\Tests\CompilerServices;

use Stillat\BladeParser\Compiler\CompilerServices\StringUtilities;
use Stillat\BladeParser\Tests\ParserTestCase;

class StringUtilitiesTest extends ParserTestCase
{
    public function testWrappingDoesNotWrapDollarVariables()
    {
        $this->assertSame('$test', StringUtilities::wrapInSingleQuotes('$test'));
    }

    public function testWrappingSingleQuoteStringsDoesNotDoubleUpQuotes()
    {
        $input = "'test'";
        $this->assertSame($input, StringUtilities::wrapInSingleQuotes($input));
    }

    public function testHasTrailingWhitespaceEmptyStrings()
    {
        $this->assertFalse(StringUtilities::hasTrailingWhitespace(''));
    }

    public function testHasLeadingWhitespaceEmptyStrings()
    {
        $this->assertFalse(StringUtilities::hasLeadingWhitespace(''));
    }

    public function testBreakByNewLine()
    {
        $input = <<<'EOT'
One
Two\nThree
Four
EOT;
        $this->assertCount(3, StringUtilities::breakByNewLine($input));
    }
}
