<?php

namespace Stillat\BladeParser\Tests\Structures;

use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\LiteralNode;
use Stillat\BladeParser\Tests\ParserTestCase;

class ParentNodesTest extends ParserTestCase
{
    public function testBasicParentRelationships()
    {
        $template = <<<'EOT'
@if ('something')
    @include('something')
    Literal
    @include('something_else')
    Literal Two
    
    @while(true)
        @include('another thing')
    @endwhile
@endif
Literal
EOT;
        $doc = $this->getDocument($template);
        $doc->resolveStructures();
        /** @var AbstractNode[] $nodes */
        $nodes = $doc->getNodes()->all();
        $this->assertCount(14, $nodes);

        /** @var DirectiveNode $n1 */
        $n1 = $nodes[0];   // @if
        $n2 = $nodes[1];   // Literal
        $n3 = $nodes[2];   // @include
        $n4 = $nodes[3];   // Literal
        $n5 = $nodes[4];   // @include
        $n6 = $nodes[5];   // Literal

        /** @var DirectiveNode $n7 */
        $n7 = $nodes[6];   // While
        $n8 = $nodes[7];   // Literal
        $n9 = $nodes[8];   // @include
        $n10 = $nodes[9];  // Literal
        $n11 = $nodes[10]; // @endwhile
        $n12 = $nodes[11]; // Literal
        $n13 = $nodes[12]; // @endif
        $n14 = $nodes[13]; // Literal

        $this->assertNull($n1->parent);
        $this->assertNull($n14->parent);
        $this->assertNodesHaveParent($n1, [
            $n2, $n3, $n4, $n5, $n6, $n7,
            $n12, $n13,
        ]);

        $this->assertNodesHaveParent($n7, [
            $n8, $n9, $n10, $n11,
        ]);
    }

    public function testBasicRootNodes()
    {
        $template = <<<'EOT'
@if ('something')
    @include('something')
    Literal
    @include('something_else')
    Literal Two
    
    @while(true)
        @include('another thing')
    @endwhile
@endif
Literal Final
EOT;
        $doc = $this->getDocument($template);
        $nodes = $doc->getNodes();
        $rootNodes = $doc->getRootNodes();

        $this->assertCount(14, $nodes);
        $this->assertCount(2, $rootNodes);

        $n1 = $rootNodes[0];
        $n2 = $rootNodes[1];

        $this->assertInstanceOf(DirectiveNode::class, $n1);
        $this->assertInstanceOf(LiteralNode::class, $n2);

        $this->assertSame('if', $n1->content);
        $this->assertStringContainsString('Literal Final', $n2->content);
    }

    /**
     * @param  AbstractNode[]  $nodes
     */
    private function assertNodesHaveParent(DirectiveNode $parent, array $nodes): void
    {
        foreach ($nodes as $node) {
            $this->assertEquals($parent, $node->parent);
        }

        // Get rid of the final closing directive.
        /** @var DirectiveNode $closingDirective */
        $closingDirective = array_pop($nodes);

        $this->assertDirectivesArePaired($parent, $closingDirective);
        $this->assertCount(count($nodes), $parent->childNodes);

        // Make sure the children are in the correct order, and are the same instance.
        for ($i = 0; $i < count($nodes); $i++) {
            $this->assertEquals($nodes[$i], $parent->childNodes[$i]);
        }
    }
}
