<?php

namespace Stillat\BladeParser\Tests;

use Stillat\BladeParser\Nodes\StaticNode;
use Stillat\BladeParser\Parsers\Blade;

class BladeDirectivesTest extends ParserTestCase
{
    public function testDirectivesWithoutLeadingSpaceAreIgnored()
    {
        $template = '<input type="text" id="emailBackdrop" class="form-control" placeholder="xxxx@xxx.xx">';
        $parser = new Blade();
        $result = $parser->parse($template);
        $nodes = $result->getNodes();

        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(StaticNode::class, $nodes[0]);

        $this->assertSame('<input type="text" id="emailBackdrop" class="form-control" placeholder="xxxx@xxx.xx">', $nodes[0]->rawContent);
    }
}
