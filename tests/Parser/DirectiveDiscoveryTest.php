<?php

namespace Stillat\BladeParser\Tests\Parser;

use Stillat\BladeParser\Tests\ParserTestCase;

class DirectiveDiscoveryTest extends ParserTestCase
{
    public function testUnregisteredDirectivesAreNotParsed()
    {
        $template = '@_not_a_directive';
        $nodes = $this->parseNodes($template);
        $this->assertCount(1, $nodes);
        $this->assertLiteralContent($nodes[0], $template);

        $this->registerDirective('_not_a_directive');
        $nodes = $this->parseNodes($template);
        $this->assertCount(1, $nodes);
        $this->assertDirectiveName($nodes[0], '_not_a_directive');
    }
}
