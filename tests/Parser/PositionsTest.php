<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\LiteralNode;

test('literal document positions', function () {
    $template = <<<'EOT'
Just a literal
    that spans multiple lines.
EOT;

    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(1);
    expect($nodes[0])->toBeInstanceOf(LiteralNode::class);

    /** @var LiteralNode $literal */
    $literal = $nodes[0];

    expect($literal->position)->not->toBeNull();
    expect($literal->position->startLine)->toBe(1);
    expect($literal->position->startColumn)->toBe(1);

    expect($literal->position->endLine)->toBe(2);
    expect($literal->position->endColumn)->toBe(30);
});

test('directives mixed with literals', function () {
    $template = <<<'EOT'
Just a literal
    that spans multiple lines.
Third @if ($something) fourth

end here
EOT;
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(3);

    expect($nodes[0])->toBeInstanceOf(LiteralNode::class);
    expect($nodes[1])->toBeInstanceOf(DirectiveNode::class);
    expect($nodes[2])->toBeInstanceOf(LiteralNode::class);

    /** @var LiteralNode $firstLiteral */
    $firstLiteral = $nodes[0];
    $this->assertStartPosition($firstLiteral->position, 0, 1, 1);
    $this->assertEndPosition($firstLiteral->position, 51, 3, 6);

    /** @var DirectiveNode $directive */
    $directive = $nodes[1];
    $this->assertStartPosition($directive->position, 52, 3, 7);
    $this->assertEndPosition($directive->position, 67, 3, 22);

    expect($directive->arguments)->not->toBeNull();
    $this->assertStartPosition($directive->arguments->position, 56, 3, 11);
    $this->assertEndPosition($directive->arguments->position, 67, 3, 22);

    /** @var LiteralNode $secondLiteral */
    $secondLiteral = $nodes[2];
    $this->assertStartPosition($secondLiteral->position, 68, 3, 23);
    $this->assertEndPosition($secondLiteral->position, 84, 5, 8);
});

test('component tag positions', function () {
    $template = <<<'EOT'
Just a literal
    that spans multiple lines.
Third <x-component name="foo" /> fourth

end here
EOT;
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(3);

    expect($nodes[0])->toBeInstanceOf(LiteralNode::class);
    expect($nodes[1])->toBeInstanceOf(ComponentNode::class);
    expect($nodes[2])->toBeInstanceOf(LiteralNode::class);

    /** @var LiteralNode $literalOne */
    $literalOne = $nodes[0];
    $this->assertStartPosition($literalOne->position, 0, 1, 1);
    $this->assertEndPosition($literalOne->position, 51, 3, 6);

    /** @var ComponentNode $component */
    $component = $nodes[1];
    $this->assertStartPosition($component->position, 52, 3, 7);
    $this->assertEndPosition($component->position, 77, 3, 32);

    expect($component->parameters)->toHaveCount(1);

    $paramOne = $component->parameters[0];
    $this->assertStartPosition($paramOne->position, 65, 3, 20);
    $this->assertEndPosition($paramOne->position, 74, 3, 29);

    expect($paramOne->nameNode)->not->toBeNull();
    expect($paramOne->nameNode->position)->not->toBeNull();
    $this->assertStartPosition($paramOne->nameNode->position, 65, 3, 20);
    $this->assertEndPosition($paramOne->nameNode->position, 68, 3, 23);

    expect($paramOne->valueNode)->not->toBeNull();
    expect($paramOne->valueNode->position)->not->toBeNull();
    $this->assertStartPosition($paramOne->valueNode->position, 70, 3, 25);
    $this->assertEndPosition($paramOne->valueNode->position, 74, 3, 29);

    /** @var LiteralNode $literalTwo */
    $literalTwo = $nodes[2];
    $this->assertStartPosition($literalTwo->position, 78, 3, 33);
    $this->assertEndPosition($literalTwo->position, 94, 5, 8);
});
