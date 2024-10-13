<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\LiteralNode;

test('basic parent relationships', function () {
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
    expect($nodes)->toHaveCount(14);

    /** @var DirectiveNode $n1 */
    $n1 = $nodes[0];
    // @if
    $n2 = $nodes[1];
    // Literal
    $n3 = $nodes[2];
    // @include
    $n4 = $nodes[3];
    // Literal
    $n5 = $nodes[4];
    // @include
    $n6 = $nodes[5];

    // Literal
    /** @var DirectiveNode $n7 */
    $n7 = $nodes[6];
    // While
    $n8 = $nodes[7];
    // Literal
    $n9 = $nodes[8];
    // @include
    $n10 = $nodes[9];
    // Literal
    $n11 = $nodes[10];
    // @endwhile
    $n12 = $nodes[11];
    // Literal
    $n13 = $nodes[12];
    // @endif
    $n14 = $nodes[13];

    // Literal
    expect($n1->parent)->toBeNull();
    expect($n14->parent)->toBeNull();
    assertNodesHaveParent($n1, [
        $n2, $n3, $n4, $n5, $n6, $n7,
        $n12, $n13,
    ]);

    assertNodesHaveParent($n7, [
        $n8, $n9, $n10, $n11,
    ]);
});

test('basic root nodes', function () {
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

    expect($nodes)->toHaveCount(14);
    expect($rootNodes)->toHaveCount(2);

    $n1 = $rootNodes[0];
    $n2 = $rootNodes[1];

    expect($n1)->toBeInstanceOf(DirectiveNode::class);
    expect($n2)->toBeInstanceOf(LiteralNode::class);

    expect($n1->content)->toBe('if');
    $this->assertStringContainsString('Literal Final', $n2->content);
});

