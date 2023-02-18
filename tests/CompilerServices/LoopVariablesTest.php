<?php

namespace Stillat\BladeParser\Tests\CompilerServices;

use Stillat\BladeParser\Compiler\CompilerServices\LoopVariablesExtractor;
use Stillat\BladeParser\Tests\ParserTestCase;

class LoopVariablesTest extends ParserTestCase
{
    private LoopVariablesExtractor $extractor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extractor = new LoopVariablesExtractor();
    }

    public function testBasicLoopVariableExtraction()
    {
        $input = '($users as $user)';
        $result = $this->extractor->extractDetails($input);

        $this->assertNotNull($result);
        $this->assertSame('($users as $user)', $result->source);
        $this->assertSame('$users', $result->variable);
        $this->assertSame('$user', $result->alias);
        $this->assertSame(true, $result->isValid);
    }

    public function testNestedStringsAndKeywordsDontConfuseThings()
    {
        $input = 'explode(", ", "as,as,as,as") as $as';
        $result = $this->extractor->extractDetails($input);

        $this->assertSame('explode(", ", "as,as,as,as")', $result->variable);
        $this->assertSame('$as', $result->alias);
        $this->assertTrue($result->isValid);
    }

    /**
     * @dataProvider invalidLoopVariableSources
     */
    public function testInvalidLoopVariables($input)
    {
        $result = $this->extractor->extractDetails($input);
        $this->assertFalse($result->isValid);
    }

    public static function invalidLoopVariableSources()
    {
        return [
            ['as'],
            ['as $user'],
            ['$users as   '],
        ];
    }
}
