<?php

namespace Stillat\BladeParser\Tests\Parser;

use Stillat\BladeParser\Tests\ParserTestCase;

class DirectiveNamesTest extends ParserTestCase
{
    public function testDirectivesWithLeadingUnderscoresAreParsed()
    {
        $this->registerDirective('_test');
        $nodes = $this->parseNodes('@_test');

        $this->assertCount(1, $nodes);
        $this->assertDirectiveName($nodes[0], '_test');

        $this->registerDirective('___test');
        $nodes = $this->parseNodes('@___test');

        $this->assertCount(1, $nodes);
        $this->assertDirectiveName($nodes[0], '___test');
    }

    public function testDirectivesContainingUnderscoresAreParsed()
    {
        $this->registerDirective('_directive_test');
        $nodes = $this->parseNodes('@_directive_test');

        $this->assertCount(1, $nodes);
        $this->assertDirectiveName($nodes[0], '_directive_test');
    }

    public function testDirectivesWithTrailingUnderscore()
    {
        $this->registerDirective('test_');
        $nodes = $this->parseNodes('@test_');

        $this->assertCount(1, $nodes);
        $this->assertDirectiveName($nodes[0], 'test_');
    }

    public function testDirectiveNamesWithDoubleColons()
    {
        $this->registerDirective('test::directive');
        $nodes = $this->parseNodes('@test::directive');

        $this->assertCount(1, $nodes);
        $this->assertDirectiveName($nodes[0], 'test::directive');
    }

    public function testCamelCasedDirectiveNames()
    {
        $this->registerDirective('testDirective');
        $nodes = $this->parseNodes('@testDirective');

        $this->assertCount(1, $nodes);
        $this->assertDirectiveName($nodes[0], 'testDirective');
    }
}
