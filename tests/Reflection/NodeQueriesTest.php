<?php

namespace Stillat\BladeParser\Tests\Reflection;

use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\EchoNode;
use Stillat\BladeParser\Nodes\LiteralNode;
use Stillat\BladeParser\Nodes\PhpTagNode;
use Stillat\BladeParser\Tests\ParserTestCase;

class NodeQueriesTest extends ParserTestCase
{
    public function testAllNotOfType()
    {
        $template = <<<'EOT'
Literal One {{ $echo }} @lang {{ $echo }}
EOT;
        $doc = $this->getDocument($template);
        $nodes = $doc->allNotOfType(DirectiveNode::class);
        $this->assertCount(5, $nodes);

        $this->assertInstanceOf(LiteralNode::class, $nodes[0]);
        $this->assertInstanceOf(EchoNode::class, $nodes[1]);
        $this->assertInstanceOf(LiteralNode::class, $nodes[2]);
        $this->assertInstanceOf(LiteralNode::class, $nodes[3]);
        $this->assertInstanceOf(EchoNode::class, $nodes[4]);
    }

    public function testFirstOfTypeCanReturnNull()
    {
        $template = <<<'EOT'
Literal One {{ $echo }} @lang {{ $echo }}
EOT;
        $doc = $this->getDocument($template);
        $this->assertNull($doc->firstOfType(PhpTagNode::class));
    }

    public function testFindAllNodesStartingOnLine()
    {
        $template = <<<'EOT'
Literal One {{ $echo }} @lang {{ $echo }}
    @if ('something')    @endif
EOT;
        $doc = $this->getDocument($template);
        $nodes = $doc->findAllNodesStartingOnLine(2);
        $this->assertCount(3, $nodes);
        $this->assertInstanceOf(DirectiveNode::class, $nodes[0]);
        $this->assertInstanceOf(LiteralNode::class, $nodes[1]);
        $this->assertInstanceOf(DirectiveNode::class, $nodes[2]);
    }

    public function testQueryingSwitchStatements()
    {
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
        $this->assertCount(2, $doc->getAllSwitchStatements());
        $if = $doc->findDirectiveByName('if');

        $this->assertCount(1, $if->getRootSwitchStatements());
    }

    public function testQueryingConditions()
    {
        $template = <<<'EOT'
@if ($something)
    @if ($somethingElse)
    
    @endif
@endif

@if ($anotherThing)

@endif
EOT;
        $doc = $this->getDocument($template)->resolveStructures();
        $this->assertCount(3, $doc->getAllConditions());
        $if = $doc->findDirectiveByName('if');

        $this->assertCount(1, $if->getRootConditions());
    }

    public function testQueryingForElse()
    {
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
        $this->assertCount(2, $doc->getAllForElse());
        $if = $doc->findDirectiveByName('if');

        $this->assertCount(1, $if->getRootForElse());
    }

    public function testFindNodePattern()
    {
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

        $this->assertCount(2, $patternOneResults);

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

        $this->assertCount(1, $patternTwoResults);
        $ex3 = $patternTwoResults[0];
        $this->assertDirectiveContent($ex3[0], 'include', "('something-else')");
        $this->assertEchoContent($ex3[2], '{{ $hello }}');
        $this->assertEchoContent($ex3[4], '{{ $world }}');
        $this->assertDirectiveContent($ex3[6], 'if', '($something)');

        $patternThreeResults = $doc->findNodePattern([
            DirectiveNode::class,
            EchoNode::class,
        ]);
        $this->assertCount(2, $patternThreeResults);

        $ex4 = $patternThreeResults[0];
        $this->assertDirectiveContent($ex4[0], 'include', "('something-else')");
        $this->assertEchoContent($ex4[2], '{{ $hello }}');

        $ex5 = $patternThreeResults[1];
        $this->assertDirectiveContent($ex5[0], 'if', '($something)');
        $this->assertEchoContent($ex5[2], '{{ $greetings }}');
    }
}
