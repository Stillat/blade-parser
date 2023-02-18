<?php

namespace Stillat\BladeParser\Tests\Structures;

use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\LiteralNode;
use Stillat\BladeParser\Tests\ParserTestCase;

class ComponentTagChildrenTest extends ParserTestCase
{
    public function testBasicComponentTagChildren()
    {
        $template = <<<'EOT'
One <x-alert> One @include('test') Two </x-alert> Three
EOT;
        $doc = $this->getDocument($template);
        $doc->resolveStructures();
        /** @var ComponentNode $alert */
        $alert = $doc->findComponentsByTagName('alert')->first();

        $this->assertNotNull($alert);
        $this->assertCount(3, $alert->childNodes);
        $this->assertInstanceOf(LiteralNode::class, $alert->childNodes[0]);
        $this->assertSame(' One ', $alert->childNodes[0]->content);
        $this->assertInstanceOf(DirectiveNode::class, $alert->childNodes[1]);
        $this->assertDirectiveContent($alert->childNodes[1], 'include', "('test')");
        $this->assertInstanceOf(LiteralNode::class, $alert->childNodes[2]);
        $this->assertSame(' Two ', $alert->childNodes[2]->content);
    }
}
