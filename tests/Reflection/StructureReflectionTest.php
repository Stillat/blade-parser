<?php

namespace Stillat\BladeParser\Tests\Reflection;

use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\EchoNode;
use Stillat\BladeParser\Nodes\LiteralNode;
use Stillat\BladeParser\Tests\ParserTestCase;

class StructureReflectionTest extends ParserTestCase
{
    public function testBasicFalseStructureTests()
    {
        $doc = $this->getDocument('@lang("something")');
        $directive = $doc->findDirectiveByName('lang');

        foreach ([
            EchoNode::class,
            LiteralNode::class,
            DirectiveNode::class,
        ] as $type) {
            $this->assertFalse($directive->hasParentOfType($type));
        }

        $this->assertCount(0, $directive->getAllParentNodes());
        $this->assertFalse($directive->hasConditionParent());
        $this->assertFalse($directive->hasForElseParent());
        $this->assertFalse($directive->hasSwitchParent());
        $this->assertFalse($directive->hasParent());
        $this->assertFalse($directive->hasStructure());
    }

    public function testBasicForElseStructureTests()
    {
        $template = <<<'EOT'
@forelse ($users as $user)
    @lang("something")
@empty
    Nothing to see here.
@endforelse 
EOT;
        $doc = $this->getDocument($template)->resolveStructures();
        $directive = $doc->findDirectiveByName('lang');

        $this->assertTrue($directive->hasParentOfType(DirectiveNode::class));
        $this->assertTrue($directive->hasParent());
        $this->assertTrue($directive->hasForElseParent());
        $this->assertTrue($directive->getParent()->isStructure);
        $this->assertNotNull($directive->getParent()->asDirective()->getForElse());
    }

    public function testBasicConditionStructureTests()
    {
        $template = <<<'EOT'
@if ($something)
    @lang("something")
@endif
EOT;
        $doc = $this->getDocument($template)->resolveStructures();
        $directive = $doc->findDirectiveByName('lang');

        $this->assertTrue($directive->hasParentOfType(DirectiveNode::class));
        $this->assertTrue($directive->hasParent());
        $this->assertTrue($directive->hasConditionParent());
        $this->assertTrue($directive->getParent()->isStructure);
        $this->assertNotNull($directive->getParent()->asDirective()->getCondition());
    }

    public function testBasicSwitchStructureTests()
    {
        $template = <<<'EOT'
@switch($something)
    @case(1)
        @lang("something")
       @break;
@endswitch
EOT;
        $doc = $this->getDocument($template)->resolveStructures();
        $directive = $doc->findDirectiveByName('lang');

        $this->assertTrue($directive->hasParentOfType(DirectiveNode::class));
        $this->assertTrue($directive->hasParent());
        $this->assertTrue($directive->hasSwitchParent());
        $this->assertTrue($directive->getParent()->isStructure);
        $this->assertNotNull($directive->getParent()->asDirective()->getCaseStatement());
        $this->assertNotNull($directive->getParent()->asDirective()->getCaseStatement()->getParent()->asDirective()->getSwitchStatement());
    }
}
