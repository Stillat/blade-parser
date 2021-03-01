<?php

namespace Stillat\BladeParser\Tests\Structures;

use Stillat\BladeParser\Parsers\Structures\PhpBlockParser;
use Stillat\BladeParser\Tests\ParserTestCase;

class PhpTest extends ParserTestCase
{

    public function testThatLiteralPositionsAreDiscovered()
    {
        $parser = new PhpBlockParser();
        $parser->setTokens(mb_str_split('@php  @php @php() @php'));
        $parser->parse();

        $this->assertTrue($parser->isLiteralPhp(0));
        $this->assertTrue($parser->isLiteralPhp(6));
        $this->assertTrue($parser->isLiteralPhp(18));
        $this->assertFalse($parser->isLiteralPhp(11));
    }

    public function testPairsAreCreated()
    {
        $parser = new PhpBlockParser();

        // This will actually output invalid PHP code, but is allowed
        // by the current Laravel Blade parser/compiler/engine.
        $parser->setTokens(mb_str_split('@php @php @php $counter++;
@endphp'));
        $parser->parse();

        $tagPairs = $parser->getPairs();

        $this->assertCount(1, $tagPairs);
        $this->assertTrue($parser->isInvalidLiteralLocation(0));
        $this->assertTrue($parser->isInvalidLiteralLocation(5));
        $this->assertTrue($parser->isInvalidLiteralLocation(10));

        $this->assertFalse($parser->isLiteralPhp(0));
        $this->assertFalse($parser->isLiteralPhp(5));
        $this->assertFalse($parser->isLiteralPhp(10));

        $parser->setTokens(mb_str_split('@php
$counter += 1;
@endphp @php
$counter += 2;
@endphp'));
        $parser->parse();

        $tagPairs = $parser->getPairs();
        $this->assertCount(2, $tagPairs);
        $this->assertTrue($parser->isInvalidLiteralLocation(0));
        $this->assertTrue($parser->isInvalidLiteralLocation(20));
        $this->assertTrue($parser->isInvalidLiteralLocation(28));
        $this->assertTrue($parser->isInvalidLiteralLocation(48));

        $parser->setTokens(mb_str_split('@php
$counter += 1;
@endphp @php
$counter += 2;
@endphp @php $counter += 3; @endphp'));
        $parser->parse();

        $tagPairs = $parser->getPairs();
        $this->assertCount(3, $tagPairs);

        $parser->setTokens(mb_str_split('@php @php
$counter += 1;
@endphp @php
$counter += 2;
@endphp @php @php @php @php $counter += 3; @endphp'));
        $parser->parse();

        $tagPairs = $parser->getPairs();
        $this->assertCount(3, $tagPairs);
    }

    public function testThatOutputMatchesLaravelOutput()
    {
        $string = '@php $counter++;
for($i = 0; $i++;$=) {}
@endphp @php $counter_two++;
for($i = 0; $i++;$=two) {}
@endphp';
        $expected = '<?php $counter++;
for($i = 0; $i++;$=) {}
?> <?php $counter_two++;
for($i = 0; $i++;$=two) {}
?>';
        $this->assertSame($expected, $this->compiler->compileString($string));
    }

}