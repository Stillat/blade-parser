<?php

namespace Stillat\BladeParser\Tests\Parser;

use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\LiteralNode;
use Stillat\BladeParser\Tests\ParserTestCase;

class PositionsTest extends ParserTestCase
{
    public function testLiteralDocumentPositions()
    {
        $template = <<<'EOT'
Just a literal
    that spans multiple lines.
EOT;

        $nodes = $this->parseNodes($template);
        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(LiteralNode::class, $nodes[0]);

        /** @var LiteralNode $literal */
        $literal = $nodes[0];

        $this->assertNotNull($literal->position);
        $this->assertSame(1, $literal->position->startLine);
        $this->assertSame(1, $literal->position->startColumn);

        $this->assertSame(2, $literal->position->endLine);
        $this->assertSame(30, $literal->position->endColumn);
    }

    public function testDirectivesMixedWithLiterals()
    {
        $template = <<<'EOT'
Just a literal
    that spans multiple lines.
Third @if ($something) fourth

end here
EOT;
        $nodes = $this->parseNodes($template);
        $this->assertCount(3, $nodes);

        $this->assertInstanceOf(LiteralNode::class, $nodes[0]);
        $this->assertInstanceOf(DirectiveNode::class, $nodes[1]);
        $this->assertInstanceOf(LiteralNode::class, $nodes[2]);

        /** @var LiteralNode $firstLiteral */
        $firstLiteral = $nodes[0];
        $this->assertStartPosition($firstLiteral->position, 0, 1, 1);
        $this->assertEndPosition($firstLiteral->position, 51, 3, 6);

        /** @var DirectiveNode $directive */
        $directive = $nodes[1];
        $this->assertStartPosition($directive->position, 52, 3, 7);
        $this->assertEndPosition($directive->position, 67, 3, 22);

        $this->assertNotNull($directive->arguments);
        $this->assertStartPosition($directive->arguments->position, 56, 3, 11);
        $this->assertEndPosition($directive->arguments->position, 67, 3, 22);

        /** @var LiteralNode $secondLiteral */
        $secondLiteral = $nodes[2];
        $this->assertStartPosition($secondLiteral->position, 68, 3, 23);
        $this->assertEndPosition($secondLiteral->position, 84, 5, 8);
    }

    public function testComponentTagPositions()
    {
        $template = <<<'EOT'
Just a literal
    that spans multiple lines.
Third <x-component name="foo" /> fourth

end here
EOT;
        $nodes = $this->parseNodes($template);
        $this->assertCount(3, $nodes);

        $this->assertInstanceOf(LiteralNode::class, $nodes[0]);
        $this->assertInstanceOf(ComponentNode::class, $nodes[1]);
        $this->assertInstanceOf(LiteralNode::class, $nodes[2]);

        /** @var LiteralNode $literalOne */
        $literalOne = $nodes[0];
        $this->assertStartPosition($literalOne->position, 0, 1, 1);
        $this->assertEndPosition($literalOne->position, 51, 3, 6);

        /** @var ComponentNode $component */
        $component = $nodes[1];
        $this->assertStartPosition($component->position, 52, 3, 7);
        $this->assertEndPosition($component->position, 77, 3, 32);

        $this->assertCount(1, $component->parameters);

        $paramOne = $component->parameters[0];
        $this->assertStartPosition($paramOne->position, 65, 3, 20);
        $this->assertEndPosition($paramOne->position, 74, 3, 29);

        $this->assertNotNull($paramOne->nameNode);
        $this->assertNotNull($paramOne->nameNode->position);
        $this->assertStartPosition($paramOne->nameNode->position, 65, 3, 20);
        $this->assertEndPosition($paramOne->nameNode->position, 68, 3, 23);

        $this->assertNotNull($paramOne->valueNode);
        $this->assertNotNull($paramOne->valueNode->position);
        $this->assertStartPosition($paramOne->valueNode->position, 70, 3, 25);
        $this->assertEndPosition($paramOne->valueNode->position, 74, 3, 29);

        /** @var LiteralNode $literalTwo */
        $literalTwo = $nodes[2];
        $this->assertStartPosition($literalTwo->position, 78, 3, 33);
        $this->assertEndPosition($literalTwo->position, 94, 5, 8);
    }
}
