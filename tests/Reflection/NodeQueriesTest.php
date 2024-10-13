<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\EchoNode;
use Stillat\BladeParser\Nodes\LiteralNode;
use Stillat\BladeParser\Nodes\PhpTagNode;

test('all not of type', function () {
    $template = <<<'EOT'
Literal One {{ $echo }} @lang {{ $echo }}
EOT;
    $doc = $this->getDocument($template);
    $nodes = $doc->allNotOfType(DirectiveNode::class);
    expect($nodes)->toHaveCount(5);

    expect($nodes[0])->toBeInstanceOf(LiteralNode::class);
    expect($nodes[1])->toBeInstanceOf(EchoNode::class);
    expect($nodes[2])->toBeInstanceOf(LiteralNode::class);
    expect($nodes[3])->toBeInstanceOf(LiteralNode::class);
    expect($nodes[4])->toBeInstanceOf(EchoNode::class);
});

test('first of type can return null', function () {
    $template = <<<'EOT'
Literal One {{ $echo }} @lang {{ $echo }}
EOT;
    $doc = $this->getDocument($template);
    expect($doc->firstOfType(PhpTagNode::class))->toBeNull();
});

test('find all nodes starting on line', function () {
    $template = <<<'EOT'
Literal One {{ $echo }} @lang {{ $echo }}
    @if ('something')    @endif
EOT;
    $doc = $this->getDocument($template);
    $nodes = $doc->findAllNodesStartingOnLine(2);
    expect($nodes)->toHaveCount(3);
    expect($nodes[0])->toBeInstanceOf(DirectiveNode::class);
    expect($nodes[1])->toBeInstanceOf(LiteralNode::class);
    expect($nodes[2])->toBeInstanceOf(DirectiveNode::class);
});

test('querying switch statements', function () {
    $template = <<<'EOT'
@if ($something)
    @switch($value)
       @case(1)
       
       @break
    @endswitch
@endif

@switch($value)
   @case(1)
   
   @break
@endswitch
EOT;
    $doc = $this->getDocument($template)->resolveStructures();
    expect($doc->getAllSwitchStatements())->toHaveCount(2);
    $if = $doc->findDirectiveByName('if');

    expect($if->getRootSwitchStatements())->toHaveCount(1);
});

test('querying conditions', function () {
    $template = <<<'EOT'
@if ($something)
    @if ($somethingElse)
    
    @endif
@endif

@if ($anotherThing)

@endif
EOT;
    $doc = $this->getDocument($template)->resolveStructures();
    expect($doc->getAllConditions())->toHaveCount(3);
    $if = $doc->findDirectiveByName('if');

    expect($if->getRootConditions())->toHaveCount(1);
});

test('querying for else', function () {
    $template = <<<'EOT'
@if ($something)
    @forelse ($users as $user)
       
    @empty
    
    @endforelse
@endif

@forelse ($records as $record)
   
@empty

@endforelse
EOT;
    $doc = $this->getDocument($template)->resolveStructures();
    expect($doc->getAllForElse())->toHaveCount(2);
    $if = $doc->findDirectiveByName('if');

    expect($if->getRootForElse())->toHaveCount(1);
});

test('find node pattern', function () {
    $template = <<<'EOT'
D0 @include ('something')
D1 @include ('something-else')
E0 {{ $hello }}
E1 {{ $world }}
D2 @if ($something)
E2 {{ $greetings }}
D3 @elseif 
EOT;
    $doc = $this->getDocument($template);
    $allNodes = $doc->getNodes();

    $patternOneResults = $doc->findNodePattern([
        EchoNode::class,
        DirectiveNode::class,
    ]);

    expect($patternOneResults)->toHaveCount(2);

    $ex1 = $patternOneResults[0]->all();
    $this->assertEchoContent($ex1[0], '{{ $world }}');
    $this->assertDirectiveContent($ex1[2], 'if', '($something)');

    $ex2 = $patternOneResults[1]->all();
    $this->assertEchoContent($ex2[0], '{{ $greetings }}');
    $this->assertDirectiveContent($ex2[2], 'elseif');

    $patternTwoResults = $doc->findNodePattern([
        DirectiveNode::class,
        EchoNode::class,
        EchoNode::class,
        DirectiveNode::class,
    ]);

    expect($patternTwoResults)->toHaveCount(1);
    $ex3 = $patternTwoResults[0];
    $this->assertDirectiveContent($ex3[0], 'include', "('something-else')");
    $this->assertEchoContent($ex3[2], '{{ $hello }}');
    $this->assertEchoContent($ex3[4], '{{ $world }}');
    $this->assertDirectiveContent($ex3[6], 'if', '($something)');

    $patternThreeResults = $doc->findNodePattern([
        DirectiveNode::class,
        EchoNode::class,
    ]);
    expect($patternThreeResults)->toHaveCount(2);

    $ex4 = $patternThreeResults[0];
    $this->assertDirectiveContent($ex4[0], 'include', "('something-else')");
    $this->assertEchoContent($ex4[2], '{{ $hello }}');

    $ex5 = $patternThreeResults[1];
    $this->assertDirectiveContent($ex5[0], 'if', '($something)');
    $this->assertEchoContent($ex5[2], '{{ $greetings }}');
});
