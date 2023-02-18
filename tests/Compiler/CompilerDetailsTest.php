<?php

namespace Stillat\BladeParser\Tests\Compiler;

use Stillat\BladeParser\Tests\ParserTestCase;

class CompilerDetailsTest extends ParserTestCase
{
    public function testGetSetEchoHandlers()
    {
        $handlers = [
            'test',
        ];

        $this->assertCount(0, $this->compiler->getEchoHandlers());
        $this->compiler->setEchoHandlers($handlers);
        $this->assertCount(1, $this->compiler->getEchoHandlers());

        $this->assertSame($handlers, $this->compiler->getEchoHandlers());
    }

    public function testGetSetParserStrictness()
    {
        $this->assertFalse($this->compiler->getParserErrorsIsStrict());
        $this->compiler->setParserErrorsIsStrict(true);
        $this->assertTrue($this->compiler->getParserErrorsIsStrict());
    }

    public function testGetSetCompilesComponentTags()
    {
        $this->assertTrue($this->compiler->getCompilesComponentTags());
        $this->compiler->setCompilesComponentTags(false);
        $this->assertFalse($this->compiler->getCompilesComponentTags());
    }

    public function testGetSetConditions()
    {
        $conditions = [
            'cond',
        ];

        $this->assertCount(0, $this->compiler->getConditions());
        $this->compiler->setConditions($conditions);
        $this->assertCount(1, $this->compiler->getConditions());

        $this->assertSame($conditions, $this->compiler->getConditions());
    }

    public function testGetSetPrecompilers()
    {
        $this->assertCount(0, $this->compiler->getPrecompilers());
        $this->compiler->precompiler(fn ($s) => $s);
        $this->assertCount(1, $this->compiler->getPrecompilers());
    }
}
