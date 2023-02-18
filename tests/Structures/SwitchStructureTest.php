<?php

namespace Stillat\BladeParser\Tests\Structures;

use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\LiteralNode;
use Stillat\BladeParser\Nodes\Structures\CaseStatement;
use Stillat\BladeParser\Nodes\Structures\SwitchStatement;
use Stillat\BladeParser\Tests\ParserTestCase;

class SwitchStructureTest extends ParserTestCase
{
    public function testBasicSwitchStatements()
    {
        $template = <<<'EOT'
@switch($i)
    @case(1)
        First case...
        @break
 
    @case(2)
        Second case...
        @break
 
    @default
        Default case...
@endswitch
EOT;
        $doc = $this->getDocument($template);
        $doc->resolveStructures();

        $switchDirective = $doc->findDirectiveByName('switch');
        $this->assertNotNull($switchDirective);
        $this->assertNotNull($switchDirective->structure);
        $this->assertTrue($switchDirective->isStructure);
        $this->assertInstanceOf(SwitchStatement::class, $switchDirective->structure);

        /** @var SwitchStatement $switch */
        $switch = $switchDirective->structure;
        $this->assertCount(1, $switch->leadingNodes);
        $this->assertCount(1, $switch->getLeadingNodes());
        $this->assertCount(3, $switch->cases);
        $this->assertFalse($switch->hasInvalidCases());
        $this->assertTrue($switch->hasDefaultCase());
        $this->assertSame(2, $switch->getCaseCount());
        $this->assertSame(1, $switch->getDefaultCount());

        $firstCase = $switch->cases[0];
        $this->assertCount(3, $firstCase->caseBody);
        $this->assertDirectiveContent($firstCase->getCondition(), 'case', '(1)');
        $this->assertInstanceOf(LiteralNode::class, $firstCase->caseBody[0]);
        $this->assertStringContainsString('First case...', $firstCase->caseBody[0]->content);
        $this->assertInstanceOf(DirectiveNode::class, $firstCase->caseBody[1]);
        $this->assertDirectiveContent($firstCase->caseBody[1], 'break');
        $this->assertInstanceOf(LiteralNode::class, $firstCase->caseBody[2]);

        $secondCase = $switch->cases[1];
        $this->assertCount(3, $secondCase->caseBody);
        $this->assertDirectiveContent($secondCase->getCondition(), 'case', '(2)');
        $this->assertInstanceOf(LiteralNode::class, $secondCase->caseBody[0]);
        $this->assertStringContainsString('Second case...', $secondCase->caseBody[0]->content);
        $this->assertInstanceOf(DirectiveNode::class, $secondCase->caseBody[1]);
        $this->assertDirectiveContent($secondCase->caseBody[1], 'break');
        $this->assertInstanceOf(LiteralNode::class, $secondCase->caseBody[2]);

        $thirdCase = $switch->cases[2];
        $this->assertCount(1, $thirdCase->caseBody);
        $this->assertDirectiveContent($thirdCase->getCondition(), 'default');
        $this->assertInstanceOf(LiteralNode::class, $thirdCase->caseBody[0]);
        $this->assertStringContainsString('Default case...', $thirdCase->caseBody[0]->content);
    }

    public function testNestedSwitchStatements()
    {
        $template = <<<'EOT'
@switch($i)
    @case(1)
        First case...
        @break
 
    @case(2)
        Second case...
        
            @switch($i2)
                @case(2.1)
                    First case two...
                    @break
             
                @case(2.2)
                    Second case two...
                    @break
             
                @default
                    Default case two...
            @endswitch
        
        @break
 
    @default
        Default case...
@endswitch
EOT;
        $doc = $this->getDocument($template);
        $doc->resolveStructures();

        $switchStatements = $doc->findDirectivesByName('switch');
        $this->assertCount(2, $switchStatements);

        $switchDirective = $switchStatements[0];
        $this->assertNotNull($switchDirective);
        $this->assertNotNull($switchDirective->structure);
        $this->assertTrue($switchDirective->isStructure);
        $this->assertInstanceOf(SwitchStatement::class, $switchDirective->structure);

        /** @var SwitchStatement $switch */
        $switch = $switchDirective->structure;
        $this->assertCount(1, $switch->leadingNodes);
        $this->assertCount(3, $switch->cases);
        $this->assertFalse($switch->hasInvalidCases());
        $this->assertTrue($switch->hasDefaultCase());
        $this->assertSame(2, $switch->getCaseCount());
        $this->assertSame(1, $switch->getDefaultCount());

        $firstCase = $switch->cases[0];
        $this->assertCount(3, $firstCase->caseBody);
        $this->assertDirectiveContent($firstCase->getCondition(), 'case', '(1)');
        $this->assertInstanceOf(LiteralNode::class, $firstCase->caseBody[0]);
        $this->assertStringContainsString('First case...', $firstCase->caseBody[0]->content);
        $this->assertInstanceOf(DirectiveNode::class, $firstCase->caseBody[1]);
        $this->assertDirectiveContent($firstCase->caseBody[1], 'break');
        $this->assertInstanceOf(LiteralNode::class, $firstCase->caseBody[2]);

        $secondCase = $switch->cases[1];
        $this->assertCount(5, $secondCase->caseBody);
        $this->assertDirectiveContent($secondCase->getCondition(), 'case', '(2)');
        $this->assertInstanceOf(LiteralNode::class, $secondCase->caseBody[0]);
        $this->assertStringContainsString('Second case...', $secondCase->caseBody[0]->content);
        $this->assertInstanceOf(DirectiveNode::class, $secondCase->caseBody[1]);
        $this->assertDirectiveContent($secondCase->caseBody[1], 'switch');
        $this->assertInstanceOf(LiteralNode::class, $secondCase->caseBody[2]);
        $this->assertDirectiveContent($secondCase->caseBody[3], 'break');
        $this->assertInstanceOf(LiteralNode::class, $secondCase->caseBody[4]);

        $thirdCase = $switch->cases[2];
        $this->assertCount(1, $thirdCase->caseBody);
        $this->assertDirectiveContent($thirdCase->getCondition(), 'default');
        $this->assertInstanceOf(LiteralNode::class, $thirdCase->caseBody[0]);
        $this->assertStringContainsString('Default case...', $thirdCase->caseBody[0]->content);

        $switchDirective = $switchStatements[1];
        $this->assertNotNull($switchDirective);
        $this->assertNotNull($switchDirective->structure);
        $this->assertTrue($switchDirective->isStructure);
        $this->assertInstanceOf(SwitchStatement::class, $switchDirective->structure);

        /** @var SwitchStatement $switch */
        $switch = $switchDirective->structure;
        $this->assertCount(1, $switch->leadingNodes);
        $this->assertCount(3, $switch->cases);
        $this->assertFalse($switch->hasInvalidCases());
        $this->assertTrue($switch->hasDefaultCase());
        $this->assertSame(2, $switch->getCaseCount());
        $this->assertSame(1, $switch->getDefaultCount());

        $firstCase = $switch->cases[0];
        $this->assertCount(3, $firstCase->caseBody);
        $this->assertDirectiveContent($firstCase->getCondition(), 'case', '(2.1)');
        $this->assertInstanceOf(LiteralNode::class, $firstCase->caseBody[0]);
        $this->assertStringContainsString('First case two...', $firstCase->caseBody[0]->content);
        $this->assertInstanceOf(DirectiveNode::class, $firstCase->caseBody[1]);
        $this->assertDirectiveContent($firstCase->caseBody[1], 'break');
        $this->assertInstanceOf(LiteralNode::class, $firstCase->caseBody[2]);

        $secondCase = $switch->cases[1];
        $this->assertCount(3, $secondCase->caseBody);
        $this->assertDirectiveContent($secondCase->getCondition(), 'case', '(2.2)');
        $this->assertInstanceOf(LiteralNode::class, $secondCase->caseBody[0]);
        $this->assertStringContainsString('Second case two...', $secondCase->caseBody[0]->content);
        $this->assertInstanceOf(DirectiveNode::class, $secondCase->caseBody[1]);
        $this->assertDirectiveContent($secondCase->caseBody[1], 'break');
        $this->assertInstanceOf(LiteralNode::class, $secondCase->caseBody[2]);

        $thirdCase = $switch->cases[2];
        $this->assertCount(1, $thirdCase->caseBody);
        $this->assertDirectiveContent($thirdCase->getCondition(), 'default');
        $this->assertInstanceOf(LiteralNode::class, $thirdCase->caseBody[0]);
        $this->assertStringContainsString('Default case two...', $thirdCase->caseBody[0]->content);
    }

    public function testDeeplyNestedSwitchStatements()
    {
        $template = <<<'EOT'
@switch($i)
    @case(1-2)
        First case...
 
         @switch($i2)
            @case(1.1)
                First nested case...
                @break
         
            @case(2.2)
                Second nested case...
                @break
         
            @default
                Default nested case...
        @endswitch
        
        @break
 
    @case(2)
        Second case...
                
            @switch($i3)
                @case(3.1)
                    First nested case two...
                    @break
             
                @case(3.2)
                    Second nested case two...
                    @break
             
                @default
                    Default case two...
            @endswitch
        
        @break
 
    @default
        Default case...
        
        @switch($i4)
            @case(4.1)
                First case...
                
                @switch($i5)
                    @case(5.1)
                        First case...
                        @break
                 
                    @case(5.2)
                        Second case...
                        @break
                 
                    @default
                        Default case...
                @endswitch
                
                @break
         
            @case(4.2)
                Second case...
                @break
         
            @default
                Default case...
        @endswitch
@endswitch
EOT;
        $doc = $this->getDocument($template);
        $doc->resolveStructures();
        $switchStatements = $doc->findDirectivesByName('switch');
        $this->assertCount(5, $switchStatements);

        /** @var DirectiveNode $statement */
        foreach ($switchStatements as $statement) {
            $this->assertNotNull($statement->structure);
            $this->assertInstanceOf(SwitchStatement::class, $statement->structure);
        }

        /** @var SwitchStatement $switchOne */
        $switchOne = $switchStatements[0]->structure;
        $this->assertCount(3, $switchOne->getCases());
        $this->assertSame(2, $switchOne->getCaseCount());
        $this->assertCount(2, $switchOne->getConditionCases());
        $this->assertSame(1, $switchOne->getDefaultCount());
        $this->assertCount(1, $switchOne->getDefaultCases());

        /** @var CaseStatement $defaultOne */
        $defaultOne = $switchOne->getDefaultCases()->first();
        $this->assertNotNull($defaultOne);

        $defaultOneNode = $defaultOne->getNode();
        $this->assertCount(3, $defaultOneNode->getDirectChildren());
        $defaultOneChildren = $defaultOneNode->getDirectChildren();
        $this->assertInstanceOf(LiteralNode::class, $defaultOneChildren[0]);
        $this->assertInstanceOf(DirectiveNode::class, $defaultOneChildren[1]);
        $this->assertDirectiveContent($defaultOneChildren[1], 'switch', '($i4)');
        $this->assertInstanceOf(LiteralNode::class, $defaultOneChildren[2]);

        $this->assertNotNull($defaultOneChildren[1]->structure);
        $this->assertInstanceOf(SwitchStatement::class, $defaultOneChildren[1]->structure);

        /** @var SwitchStatement $ns1 */
        $ns1 = $defaultOneChildren[1]->structure;
        $this->assertCount(2, $ns1->getConditionCases());
        $this->assertCount(1, $ns1->getDefaultCases());
        $ns1SwChildren = $ns1->getConditionCases()->first()->getNode()->getDirectChildren();
        $this->assertCount(5, $ns1SwChildren);
        $this->assertInstanceOf(LiteralNode::class, $ns1SwChildren[0]);
        $this->assertInstanceOf(DirectiveNode::class, $ns1SwChildren[1]);
        $this->assertDirectiveContent($ns1SwChildren[1], 'switch', '($i5)');
        $this->assertInstanceOf(LiteralNode::class, $ns1SwChildren[2]);
        $this->assertInstanceOf(DirectiveNode::class, $ns1SwChildren[3]);
        $this->assertDirectiveContent($ns1SwChildren[3], 'break');
        $this->assertInstanceOf(LiteralNode::class, $ns1SwChildren[4]);

        /** @var DirectiveNode $caseOne */
        $caseOne = $switchOne->getConditionCases()[0]->getNode();
        $c1Direct = $caseOne->getDirectChildren();
        $this->assertCount(5, $c1Direct);
        $this->assertInstanceOf(DirectiveNode::class, $c1Direct[1]);
        $this->assertDirectiveContent($c1Direct[1], 'switch', '($i2)');
        $this->assertNotNull($c1Direct[1]->structure);
        $this->assertInstanceOf(SwitchStatement::class, $c1Direct[1]->structure);

        /** @var SwitchStatement $nc2 */
        $nc2 = $c1Direct[1]->structure;
        $this->assertCount(1, $nc2->getDefaultCases());
        $this->assertCount(2, $nc2->getConditionCases());

        $nc2c1 = $nc2->cases[0];
        $this->assertDirectiveContent($nc2c1->constructedFrom, 'case', '(1.1)');
        $this->assertCount(3, $nc2c1->caseBody);
        $this->assertInstanceOf(LiteralNode::class, $nc2c1->caseBody[0]);
        $this->assertInstanceOf(DirectiveNode::class, $nc2c1->caseBody[1]);
        $this->assertInstanceOf(LiteralNode::class, $nc2c1->caseBody[2]);

        $nc2c2 = $nc2->cases[1];
        $this->assertDirectiveContent($nc2c2->constructedFrom, 'case', '(2.2)');
        $this->assertCount(3, $nc2c2->caseBody);
        $this->assertInstanceOf(LiteralNode::class, $nc2c2->caseBody[0]);
        $this->assertInstanceOf(DirectiveNode::class, $nc2c2->caseBody[1]);
        $this->assertInstanceOf(LiteralNode::class, $nc2c2->caseBody[2]);
    }

    public function testMangledCaseStatement()
    {
        $template = <<<'EOT'
@switch($i)
    @case(1)
        First case...
        @break
        @break
 
    @case(2)
        Second case...
        @break
 
    @default
        Default case...
@endswitch
EOT;
        $doc = $this->getDocument($template);
        $doc->resolveStructures();
        $case = $doc->findDirectiveByName('case')->getCaseStatement();

        $this->assertTrue($case->hasBody());
        $this->assertSame($doc->findDirectiveByName('switch')->getSwitchStatement(), $case->getSwitch());
        $this->assertCount(5, $case->caseBody);
        $this->assertInstanceOf(LiteralNode::class, $case->caseBody[0]);
        $this->assertDirectiveContent($case->caseBody[1], 'break');
        $this->assertDirectiveContent($case->caseBody[3], 'break');

        $this->assertSame(2, $case->getBreakCount());
        $this->assertFalse($case->isValid());
    }

    public function testCaseStatementsWithoutBreaksAreInvalid()
    {
        $template = <<<'EOT'
@switch($i)
    @case(1)
        First case...
@endswitch
EOT;
        $doc = $this->getDocument($template);
        $doc->resolveStructures();
        $case = $doc->findDirectiveByName('case')->getCaseStatement();
        $this->assertSame(0, $case->getBreakCount());
        $this->assertFalse($case->isValid());
        $this->assertTrue($doc->findDirectiveByName('switch')->getSwitchStatement()->hasInvalidCases());
        $this->assertFalse($doc->findDirectiveByName('switch')->getSwitchStatement()->isValid());
    }

    public function testSwitchCaseNodeQueries()
    {
        $template = <<<'EOT'
@switch($i)
    @case(1)
        First case...
        
        @forelse ($users as $user)
            <p>Something</p>
        @empty
            <p>Something else.</p>
        @endforelse
        
        @break
 
    @case(2)
        Second case...
        @break
 
    @default
        Default case...
@endswitch
EOT;
        $doc = $this->getDocument($template);
        $doc->resolveStructures();
        /** @var CaseStatement $case */
        $case = $doc->findDirectiveByName('switch')->getSwitchStatement()->getCases()->first();

        $this->assertCount(5, $case->getDirectChildren());
        $this->assertCount(6, $case->getNodes());
    }
}
