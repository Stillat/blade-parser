<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\LiteralNode;
use Stillat\BladeParser\Nodes\Structures\CaseStatement;
use Stillat\BladeParser\Nodes\Structures\SwitchStatement;

test('basic switch statements', function () {
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
    expect($switchDirective)->not->toBeNull();
    expect($switchDirective->structure)->not->toBeNull();
    expect($switchDirective->isStructure)->toBeTrue();
    expect($switchDirective->structure)->toBeInstanceOf(SwitchStatement::class);

    /** @var SwitchStatement $switch */
    $switch = $switchDirective->structure;
    expect($switch->leadingNodes)->toHaveCount(1);
    expect($switch->getLeadingNodes())->toHaveCount(1);
    expect($switch->cases)->toHaveCount(3);
    expect($switch->hasInvalidCases())->toBeFalse();
    expect($switch->hasDefaultCase())->toBeTrue();
    expect($switch->getCaseCount())->toBe(2);
    expect($switch->getDefaultCount())->toBe(1);

    $firstCase = $switch->cases[0];
    expect($firstCase->caseBody)->toHaveCount(3);
    $this->assertDirectiveContent($firstCase->getCondition(), 'case', '(1)');
    expect($firstCase->caseBody[0])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('First case...', $firstCase->caseBody[0]->content);
    expect($firstCase->caseBody[1])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($firstCase->caseBody[1], 'break');
    expect($firstCase->caseBody[2])->toBeInstanceOf(LiteralNode::class);

    $secondCase = $switch->cases[1];
    expect($secondCase->caseBody)->toHaveCount(3);
    $this->assertDirectiveContent($secondCase->getCondition(), 'case', '(2)');
    expect($secondCase->caseBody[0])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('Second case...', $secondCase->caseBody[0]->content);
    expect($secondCase->caseBody[1])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($secondCase->caseBody[1], 'break');
    expect($secondCase->caseBody[2])->toBeInstanceOf(LiteralNode::class);

    $thirdCase = $switch->cases[2];
    expect($thirdCase->caseBody)->toHaveCount(1);
    $this->assertDirectiveContent($thirdCase->getCondition(), 'default');
    expect($thirdCase->caseBody[0])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('Default case...', $thirdCase->caseBody[0]->content);
});

test('nested switch statements', function () {
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
    expect($switchStatements)->toHaveCount(2);

    $switchDirective = $switchStatements[0];
    expect($switchDirective)->not->toBeNull();
    expect($switchDirective->structure)->not->toBeNull();
    expect($switchDirective->isStructure)->toBeTrue();
    expect($switchDirective->structure)->toBeInstanceOf(SwitchStatement::class);

    /** @var SwitchStatement $switch */
    $switch = $switchDirective->structure;
    expect($switch->leadingNodes)->toHaveCount(1);
    expect($switch->cases)->toHaveCount(3);
    expect($switch->hasInvalidCases())->toBeFalse();
    expect($switch->hasDefaultCase())->toBeTrue();
    expect($switch->getCaseCount())->toBe(2);
    expect($switch->getDefaultCount())->toBe(1);

    $firstCase = $switch->cases[0];
    expect($firstCase->caseBody)->toHaveCount(3);
    $this->assertDirectiveContent($firstCase->getCondition(), 'case', '(1)');
    expect($firstCase->caseBody[0])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('First case...', $firstCase->caseBody[0]->content);
    expect($firstCase->caseBody[1])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($firstCase->caseBody[1], 'break');
    expect($firstCase->caseBody[2])->toBeInstanceOf(LiteralNode::class);

    $secondCase = $switch->cases[1];
    expect($secondCase->caseBody)->toHaveCount(5);
    $this->assertDirectiveContent($secondCase->getCondition(), 'case', '(2)');
    expect($secondCase->caseBody[0])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('Second case...', $secondCase->caseBody[0]->content);
    expect($secondCase->caseBody[1])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($secondCase->caseBody[1], 'switch');
    expect($secondCase->caseBody[2])->toBeInstanceOf(LiteralNode::class);
    $this->assertDirectiveContent($secondCase->caseBody[3], 'break');
    expect($secondCase->caseBody[4])->toBeInstanceOf(LiteralNode::class);

    $thirdCase = $switch->cases[2];
    expect($thirdCase->caseBody)->toHaveCount(1);
    $this->assertDirectiveContent($thirdCase->getCondition(), 'default');
    expect($thirdCase->caseBody[0])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('Default case...', $thirdCase->caseBody[0]->content);

    $switchDirective = $switchStatements[1];
    expect($switchDirective)->not->toBeNull();
    expect($switchDirective->structure)->not->toBeNull();
    expect($switchDirective->isStructure)->toBeTrue();
    expect($switchDirective->structure)->toBeInstanceOf(SwitchStatement::class);

    /** @var SwitchStatement $switch */
    $switch = $switchDirective->structure;
    expect($switch->leadingNodes)->toHaveCount(1);
    expect($switch->cases)->toHaveCount(3);
    expect($switch->hasInvalidCases())->toBeFalse();
    expect($switch->hasDefaultCase())->toBeTrue();
    expect($switch->getCaseCount())->toBe(2);
    expect($switch->getDefaultCount())->toBe(1);

    $firstCase = $switch->cases[0];
    expect($firstCase->caseBody)->toHaveCount(3);
    $this->assertDirectiveContent($firstCase->getCondition(), 'case', '(2.1)');
    expect($firstCase->caseBody[0])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('First case two...', $firstCase->caseBody[0]->content);
    expect($firstCase->caseBody[1])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($firstCase->caseBody[1], 'break');
    expect($firstCase->caseBody[2])->toBeInstanceOf(LiteralNode::class);

    $secondCase = $switch->cases[1];
    expect($secondCase->caseBody)->toHaveCount(3);
    $this->assertDirectiveContent($secondCase->getCondition(), 'case', '(2.2)');
    expect($secondCase->caseBody[0])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('Second case two...', $secondCase->caseBody[0]->content);
    expect($secondCase->caseBody[1])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($secondCase->caseBody[1], 'break');
    expect($secondCase->caseBody[2])->toBeInstanceOf(LiteralNode::class);

    $thirdCase = $switch->cases[2];
    expect($thirdCase->caseBody)->toHaveCount(1);
    $this->assertDirectiveContent($thirdCase->getCondition(), 'default');
    expect($thirdCase->caseBody[0])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('Default case two...', $thirdCase->caseBody[0]->content);
});

test('deeply nested switch statements', function () {
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
    expect($switchStatements)->toHaveCount(5);

    /** @var DirectiveNode $statement */
    foreach ($switchStatements as $statement) {
        expect($statement->structure)->not->toBeNull();
        expect($statement->structure)->toBeInstanceOf(SwitchStatement::class);
    }

    /** @var SwitchStatement $switchOne */
    $switchOne = $switchStatements[0]->structure;
    expect($switchOne->getCases())->toHaveCount(3);
    expect($switchOne->getCaseCount())->toBe(2);
    expect($switchOne->getConditionCases())->toHaveCount(2);
    expect($switchOne->getDefaultCount())->toBe(1);
    expect($switchOne->getDefaultCases())->toHaveCount(1);

    /** @var CaseStatement $defaultOne */
    $defaultOne = $switchOne->getDefaultCases()->first();
    expect($defaultOne)->not->toBeNull();

    $defaultOneNode = $defaultOne->getNode();
    expect($defaultOneNode->getDirectChildren())->toHaveCount(3);
    $defaultOneChildren = $defaultOneNode->getDirectChildren();
    expect($defaultOneChildren[0])->toBeInstanceOf(LiteralNode::class);
    expect($defaultOneChildren[1])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($defaultOneChildren[1], 'switch', '($i4)');
    expect($defaultOneChildren[2])->toBeInstanceOf(LiteralNode::class);

    expect($defaultOneChildren[1]->structure)->not->toBeNull();
    expect($defaultOneChildren[1]->structure)->toBeInstanceOf(SwitchStatement::class);

    /** @var SwitchStatement $ns1 */
    $ns1 = $defaultOneChildren[1]->structure;
    expect($ns1->getConditionCases())->toHaveCount(2);
    expect($ns1->getDefaultCases())->toHaveCount(1);
    $ns1SwChildren = $ns1->getConditionCases()->first()->getNode()->getDirectChildren();
    expect($ns1SwChildren)->toHaveCount(5);
    expect($ns1SwChildren[0])->toBeInstanceOf(LiteralNode::class);
    expect($ns1SwChildren[1])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($ns1SwChildren[1], 'switch', '($i5)');
    expect($ns1SwChildren[2])->toBeInstanceOf(LiteralNode::class);
    expect($ns1SwChildren[3])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($ns1SwChildren[3], 'break');
    expect($ns1SwChildren[4])->toBeInstanceOf(LiteralNode::class);

    /** @var DirectiveNode $caseOne */
    $caseOne = $switchOne->getConditionCases()[0]->getNode();
    $c1Direct = $caseOne->getDirectChildren();
    expect($c1Direct)->toHaveCount(5);
    expect($c1Direct[1])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($c1Direct[1], 'switch', '($i2)');
    expect($c1Direct[1]->structure)->not->toBeNull();
    expect($c1Direct[1]->structure)->toBeInstanceOf(SwitchStatement::class);

    /** @var SwitchStatement $nc2 */
    $nc2 = $c1Direct[1]->structure;
    expect($nc2->getDefaultCases())->toHaveCount(1);
    expect($nc2->getConditionCases())->toHaveCount(2);

    $nc2c1 = $nc2->cases[0];
    $this->assertDirectiveContent($nc2c1->constructedFrom, 'case', '(1.1)');
    expect($nc2c1->caseBody)->toHaveCount(3);
    expect($nc2c1->caseBody[0])->toBeInstanceOf(LiteralNode::class);
    expect($nc2c1->caseBody[1])->toBeInstanceOf(DirectiveNode::class);
    expect($nc2c1->caseBody[2])->toBeInstanceOf(LiteralNode::class);

    $nc2c2 = $nc2->cases[1];
    $this->assertDirectiveContent($nc2c2->constructedFrom, 'case', '(2.2)');
    expect($nc2c2->caseBody)->toHaveCount(3);
    expect($nc2c2->caseBody[0])->toBeInstanceOf(LiteralNode::class);
    expect($nc2c2->caseBody[1])->toBeInstanceOf(DirectiveNode::class);
    expect($nc2c2->caseBody[2])->toBeInstanceOf(LiteralNode::class);
});

test('mangled case statement', function () {
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

    expect($case->hasBody())->toBeTrue();
    expect($case->getSwitch())->toBe($doc->findDirectiveByName('switch')->getSwitchStatement());
    expect($case->caseBody)->toHaveCount(5);
    expect($case->caseBody[0])->toBeInstanceOf(LiteralNode::class);
    $this->assertDirectiveContent($case->caseBody[1], 'break');
    $this->assertDirectiveContent($case->caseBody[3], 'break');

    expect($case->getBreakCount())->toBe(2);
    expect($case->isValid())->toBeFalse();
});

test('case statements without breaks are invalid', function () {
    $template = <<<'EOT'
@switch($i)
    @case(1)
        First case...
@endswitch
EOT;
    $doc = $this->getDocument($template);
    $doc->resolveStructures();
    $case = $doc->findDirectiveByName('case')->getCaseStatement();
    expect($case->getBreakCount())->toBe(0);
    expect($case->isValid())->toBeFalse();
    expect($doc->findDirectiveByName('switch')->getSwitchStatement()->hasInvalidCases())->toBeTrue();
    expect($doc->findDirectiveByName('switch')->getSwitchStatement()->isValid())->toBeFalse();
});

test('switch case node queries', function () {
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

    expect($case->getDirectChildren())->toHaveCount(5);
    expect($case->getNodes())->toHaveCount(6);
});
