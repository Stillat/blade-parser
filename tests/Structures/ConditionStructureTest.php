<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\Structures\Condition;
use Stillat\BladeParser\Nodes\Structures\ConditionalBranch;

test('basic condition structures', function () {
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
    expect($if)->not->toBeNull();

    expect($if->isStructure)->toBeTrue();
    expect($if->structure)->not->toBeNull();

    /** @var Condition $structure */
    $structure = $if->structure;

    expect($structure->hasElseIfBranches())->toBeTrue();
    expect($structure->hasElseBranch())->toBeTrue();
    expect($structure->branches)->toHaveCount(3);
    expect($if)->toBe($structure->constructedFrom);
});

test('nested condition structures', function () {
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
    expect($ifStatements)->toHaveCount(2);

    /** @var DirectiveNode $ifOne */
    $ifOne = $ifStatements[0];

    /** @var DirectiveNode $ifTwo */
    $ifTwo = $ifStatements[1];

    expect($ifOne->isStructure)->toBeTrue();
    expect($ifOne->structure)->not->toBeNull();
    expect($ifTwo->isStructure)->toBeTrue();
    expect($ifTwo->structure)->not->toBeNull();

    /** @var Condition $conditionOne */
    $conditionOne = $ifOne->structure;

    /** @var Condition $conditionTwo */
    $conditionTwo = $ifTwo->structure;

    expect($conditionOne->containsDuplicateConditions())->toBeFalse();
    expect($conditionTwo->containsDuplicateConditions())->toBeFalse();

    expect($conditionOne->branches)->toHaveCount(3);
    expect($ifOne)->toBe($conditionOne->constructedFrom);

    $ifOneBranches = $conditionOne->branches;
    $this->assertDirectiveContent($ifOneBranches[0]->target, 'if', "(\$that == 'this')");
    $this->assertDirectiveContent($ifOneBranches[1]->target, 'elseif', "(\$somethingElse == 'that')");
    $this->assertDirectiveContent($ifOneBranches[2]->target, 'else');

    expect($conditionTwo->branches)->toHaveCount(4);
    expect($ifTwo)->toBe($conditionTwo->constructedFrom);

    $ifTwoBranches = $conditionTwo->branches;
    $this->assertDirectiveContent($ifTwoBranches[0]->target, 'if', "(\$that == 'this')");
    $this->assertDirectiveContent($ifTwoBranches[1]->target, 'elseif', "(\$somethingElse == 'that')");
    $this->assertDirectiveContent($ifTwoBranches[2]->target, 'elseif', "(\$anotherThing == 'some value')");
    $this->assertDirectiveContent($ifTwoBranches[3]->target, 'else');
});

test('basic condition structure details', function () {
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

    expect($if->containsDuplicateConditions())->toBeFalse();
    expect($if->getBranches())->toHaveCount(3);
    expect($if->getElseBranches())->toHaveCount(1);
    expect($if->getElseIfBranches())->toHaveCount(1);
    expect($if->isValid())->toBeTrue();
    expect($if->isUnless())->toBeFalse();
});

test('basic unless structure details', function () {
    $template = <<<'EOT'
@unless ($something)
    One
@endunless
EOT;
    $doc = $this->getDocument($template);
    $doc->resolveStructures();
    $if = $doc->findDirectiveByName('unless')->getCondition();

    expect($if->hasElseBranch())->toBeFalse();
    expect($if->hasElseIfBranches())->toBeFalse();
    expect($if->containsDuplicateConditions())->toBeFalse();
    expect($if->getBranches())->toHaveCount(1);
    expect($if->getElseBranches())->toHaveCount(0);
    expect($if->getElseIfBranches())->toHaveCount(0);
    expect($if->isValid())->toBeTrue();
    expect($if->isUnless())->toBeTrue();
});

test('retrieving condition content', function () {
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

    expect($if->containsDuplicateConditions())->toBeFalse();
    expect($if->getConditionText()->all())->toBe($expected);
});

test('condition content removes extra parentheses', function () {
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

    expect($if->getConditionText()->all())->toBe($expected);
});

test('empty branches', function () {
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

    expect($branches[0]->isEmpty())->toBeFalse();
    expect($branches[1]->isEmpty())->toBeFalse();
    expect($branches[2]->isEmpty())->toBeTrue();
    expect($branches[3]->isEmpty())->toBeTrue();
    expect($branches[4]->isEmpty())->toBeFalse();
});

test('naive duplicate condition check', function () {
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

    expect($if->containsDuplicateConditions())->toBeTrue();
});
