<?php

namespace Stillat\BladeParser\Tests\Structures;

use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\Structures\Condition;
use Stillat\BladeParser\Nodes\Structures\ConditionalBranch;
use Stillat\BladeParser\Tests\ParserTestCase;

class ConditionStructureTest extends ParserTestCase
{
    public function testBasicConditionStructures()
    {
        $template = <<<'EOT'
One
@if ($that == 'this')
    Two
@elseif ($somethingElse == 'that')
    Three
@else
    Four
@endif 
Five
EOT;
        $doc = $this->getDocument($template);
        $doc->resolveStructures();

        $if = $doc->findDirectiveByName('if');
        $this->assertNotNull($if);

        $this->assertTrue($if->isStructure);
        $this->assertNotNull($if->structure);

        /** @var Condition $structure */
        $structure = $if->structure;

        $this->assertTrue($structure->hasElseIfBranches());
        $this->assertTrue($structure->hasElseBranch());
        $this->assertCount(3, $structure->branches);
        $this->assertSame($structure->constructedFrom, $if);
    }

    public function testNestedConditionStructures()
    {
        $template = <<<'EOT'
One
@if ($that == 'this')
    Two
    
    @if ($that == 'this')
        Nested One
    @elseif ($somethingElse == 'that')
        Nested Two
    @elseif ($anotherThing == 'some value')
        Nested Three
    @else
        Nested Four
    @endif 
    
@elseif ($somethingElse == 'that')
    Three
@else
    Four
@endif 
Five
EOT;
        $doc = $this->getDocument($template);
        $doc->resolveStructures();
        $ifStatements = $doc->findDirectivesByName('if');
        $this->assertCount(2, $ifStatements);

        /** @var DirectiveNode $ifOne */
        $ifOne = $ifStatements[0];
        /** @var DirectiveNode $ifTwo */
        $ifTwo = $ifStatements[1];

        $this->assertTrue($ifOne->isStructure);
        $this->assertNotNull($ifOne->structure);
        $this->assertTrue($ifTwo->isStructure);
        $this->assertNotNull($ifTwo->structure);

        /** @var Condition $conditionOne */
        $conditionOne = $ifOne->structure;
        /** @var Condition $conditionTwo */
        $conditionTwo = $ifTwo->structure;

        $this->assertFalse($conditionOne->containsDuplicateConditions());
        $this->assertFalse($conditionTwo->containsDuplicateConditions());

        $this->assertCount(3, $conditionOne->branches);
        $this->assertSame($conditionOne->constructedFrom, $ifOne);

        $ifOneBranches = $conditionOne->branches;
        $this->assertDirectiveContent($ifOneBranches[0]->target, 'if', "(\$that == 'this')");
        $this->assertDirectiveContent($ifOneBranches[1]->target, 'elseif', "(\$somethingElse == 'that')");
        $this->assertDirectiveContent($ifOneBranches[2]->target, 'else');

        $this->assertCount(4, $conditionTwo->branches);
        $this->assertSame($conditionTwo->constructedFrom, $ifTwo);

        $ifTwoBranches = $conditionTwo->branches;
        $this->assertDirectiveContent($ifTwoBranches[0]->target, 'if', "(\$that == 'this')");
        $this->assertDirectiveContent($ifTwoBranches[1]->target, 'elseif', "(\$somethingElse == 'that')");
        $this->assertDirectiveContent($ifTwoBranches[2]->target, 'elseif', "(\$anotherThing == 'some value')");
        $this->assertDirectiveContent($ifTwoBranches[3]->target, 'else');
    }

    public function testBasicConditionStructureDetails()
    {
        $template = <<<'EOT'
@if ($something)
    One
@elseif ($somethingElse)
    Two
@else
    Three
@endif
EOT;
        $doc = $this->getDocument($template);
        $doc->resolveStructures();
        $if = $doc->findDirectiveByName('if')->getCondition();

        $this->assertFalse($if->containsDuplicateConditions());
        $this->assertCount(3, $if->getBranches());
        $this->assertCount(1, $if->getElseBranches());
        $this->assertCount(1, $if->getElseIfBranches());
        $this->assertTrue($if->isValid());
        $this->assertFalse($if->isUnless());
    }

    public function testBasicUnlessStructureDetails()
    {
        $template = <<<'EOT'
@unless ($something)
    One
@endunless
EOT;
        $doc = $this->getDocument($template);
        $doc->resolveStructures();
        $if = $doc->findDirectiveByName('unless')->getCondition();

        $this->assertFalse($if->hasElseBranch());
        $this->assertFalse($if->hasElseIfBranches());
        $this->assertFalse($if->containsDuplicateConditions());
        $this->assertCount(1, $if->getBranches());
        $this->assertCount(0, $if->getElseBranches());
        $this->assertCount(0, $if->getElseIfBranches());
        $this->assertTrue($if->isValid());
        $this->assertTrue($if->isUnless());
    }

    public function testRetrievingConditionContent()
    {
        $template = <<<'EOT'
@if ($something)
    One
@elseif ($somethingElse)
    Two
@else
    Three
@endif
EOT;
        $doc = $this->getDocument($template);
        $doc->resolveStructures();
        $if = $doc->findDirectiveByName('if')->getCondition();

        $expected = [
            '$something',
            '$somethingElse',
        ];

        $this->assertFalse($if->containsDuplicateConditions());
        $this->assertSame($expected, $if->getConditionText()->all());
    }

    public function testConditionContentRemovesExtraParentheses()
    {
        $template = <<<'EOT'
@if (((((((((((($something))))))))))))
    One
@elseif ((((((((((($somethingElse)))))))))))
    Two
@else
    Three
@endif
EOT;
        $doc = $this->getDocument($template);
        $doc->resolveStructures();
        $if = $doc->findDirectiveByName('if')->getCondition();

        $expected = [
            '$something',
            '$somethingElse',
        ];

        $this->assertSame($expected, $if->getConditionText()->all());
    }

    public function testEmptyBranches()
    {
        $template = <<<'EOT'
@if ($something)
    One
@elseif ($somethingElse)
    Two
@elseif (())
    Three
@elseif
    Four
@else
    Five
@endif
EOT;
        $doc = $this->getDocument($template);
        $doc->resolveStructures();
        /** @var ConditionalBranch[] $branches */
        $branches = $doc->findDirectiveByName('if')->getCondition()->getBranches();

        $this->assertFalse($branches[0]->isEmpty());
        $this->assertFalse($branches[1]->isEmpty());
        $this->assertTrue($branches[2]->isEmpty());
        $this->assertTrue($branches[3]->isEmpty());
        $this->assertFalse($branches[4]->isEmpty());
    }

    public function testNaiveDuplicateConditionCheck()
    {
        $template = <<<'EOT'
@if ($something)
    One
@elseif ($something)
    Two
@else
    Three
@endif
EOT;
        $doc = $this->getDocument($template);
        $doc->resolveStructures();
        $if = $doc->findDirectiveByName('if')->getCondition();

        $this->assertTrue($if->containsDuplicateConditions());
    }
}
