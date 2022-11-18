<?php

namespace Stillat\BladeParser\Tests;

use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\StaticNode;
use Stillat\BladeParser\Parsers\Blade;

class BladeDirectivesTest extends ParserTestCase
{
    public function testDirectiveParserDoesNotEnterInfiniteLoop()
    {
        $template = '<input type="text" id="emailBackdrop" class="form-control" placeholder="xxxx@xxx.xx">';
        $parser = new Blade();
        $result = $parser->parse($template);
        $nodes = $result->getNodes();

        $this->assertCount(3, $nodes);
        $this->assertInstanceOf(StaticNode::class, $nodes[0]);
        $this->assertInstanceOf(DirectiveNode::class, $nodes[1]);
        $this->assertInstanceOf(StaticNode::class, $nodes[2]);

        $this->assertSame('@xxx', $nodes[1]->rawContent);
    }
}