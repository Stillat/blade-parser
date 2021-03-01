<?php

namespace Stillat\BladeParser\Tests\Analyzers;

use PHPUnit\Framework\TestCase;
use Stillat\BladeParser\Analyzers\ArraySearch;

class ArraySearchTest extends TestCase
{
    public function testConsecutiveIntegersAreRemoved()
    {
        $input = [0, 6, 7, 12, 13, 21, 26];
        $expected = [0, 21, 26];

        $this->assertSame($expected, ArraySearch::removeConsecutiveIntegers($input));

        $input = [5, 6, 21];
        $expected = [21];

        $this->assertSame($expected, ArraySearch::removeConsecutiveIntegers($input));

        $input = [1, 2, 5, 7, 8];
        $expected = [5];

        $this->assertSame($expected, ArraySearch::removeConsecutiveIntegers($input));

        $input = [1, 2, 5, 7, 9];
        $expected = [5, 7, 9];

        $this->assertSame($expected, ArraySearch::removeConsecutiveIntegers($input));

        $input = [1, 2, 3, 4, 6, 8, 10];
        $expected = [6, 8, 10];

        $this->assertSame($expected, ArraySearch::removeConsecutiveIntegers($input));

        $input = [1, 2, 3, 4, 6, 8, 10, 12, 13, 14, 17, 19, 20, 21, 65];
        $expected = [6, 8, 10, 17, 65];

        $this->assertSame($expected, ArraySearch::removeConsecutiveIntegers($input));
    }

    public function testSequencesCanBeFound()
    {
        $haystack = mb_str_split('text @php text');
        $needle = mb_str_split('@php');

        $results = ArraySearch::search($needle, $haystack);

        $this->assertEquals([[5, 6, 7, 8]], $results);
    }

    public function testMultipleSequencesCanBeFound()
    {
        $haystack = mb_str_split('text @php text text @php');
        $needle = mb_str_split('@php');

        $results = ArraySearch::search($needle, $haystack);

        $this->assertEquals([[5, 6, 7, 8], [20, 21, 22, 23]], $results);
    }

    public function testMultipleSequencesCanBeFoundWithExcludingRepeatedFirstCharacter()
    {
        $haystack = mb_str_split('text @@php text text @php');
        $needle = mb_str_split('@php');

        $results = ArraySearch::search($needle, $haystack, true);

        $this->assertEquals([[21, 22, 23, 24]], $results);
    }

    public function testIndexTablesAreCreated()
    {
        $this->assertSame([
            21 => [21, 22, 23, 24],
        ], ArraySearch::createIndexTable([[21, 22, 23, 24]]));

        $this->assertSame([
            21 => [21, 22, 23, 24],
            30 => [30, 31, 32, 33],
        ], ArraySearch::createIndexTable([[21, 22, 23, 24], [30, 31, 32, 33]]));
    }

    public function testCanSearchInComplicatedStrings()
    {
        $expected = [
            [0, 1, 2, 3],
            [21, 22, 23, 24],
        ];

        $string = '@php

@@php @@endphp
@php @endphp
';
        $results = ArraySearch::searchStrings('@php', $string);
        $this->assertSame($expected, $results);

        $expected = [
            [0, 1, 2, 3],
            [23, 24, 25, 26],
        ];

        $string = '@php()

@@php @@endphp
@php @endphp
';
        $results = ArraySearch::searchStrings('@php', $string);
        $this->assertSame($expected, $results);
    }
}
