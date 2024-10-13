<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\EchoNode;
use Stillat\BladeParser\Nodes\LiteralNode;

test('basic false structure tests', function () {
    $doc = $this->getDocument('@lang("something")');
    $directive = $doc->findDirectiveByName('lang');

    foreach ([
        EchoNode::class,
        LiteralNode::class,
        DirectiveNode::class,
    ] as $type) {
        expect($directive->hasParentOfType($type))->toBeFalse();
    }

    expect($directive->getAllParentNodes())->toHaveCount(0);
    expect($directive->hasConditionParent())->toBeFalse();
    expect($directive->hasForElseParent())->toBeFalse();
    expect($directive->hasSwitchParent())->toBeFalse();
    expect($directive->hasParent())->toBeFalse();
    expect($directive->hasStructure())->toBeFalse();
});

test('basic for else structure tests', function () {
    $template = <<<'EOT'
@forelse ($users as $user)
    @lang("something")
@empty
    Nothing to see here.
@endforelse 
EOT;
    $doc = $this->getDocument($template)->resolveStructures();
    $directive = $doc->findDirectiveByName('lang');

    expect($directive->hasParentOfType(DirectiveNode::class))->toBeTrue();
    expect($directive->hasParent())->toBeTrue();
    expect($directive->hasForElseParent())->toBeTrue();
    expect($directive->getParent()->isStructure)->toBeTrue();
    expect($directive->getParent()->asDirective()->getForElse())->not->toBeNull();
});

test('basic condition structure tests', function () {
    $template = <<<'EOT'
@if ($something)
    @lang("something")
@endif
EOT;
    $doc = $this->getDocument($template)->resolveStructures();
    $directive = $doc->findDirectiveByName('lang');

    expect($directive->hasParentOfType(DirectiveNode::class))->toBeTrue();
    expect($directive->hasParent())->toBeTrue();
    expect($directive->hasConditionParent())->toBeTrue();
    expect($directive->getParent()->isStructure)->toBeTrue();
    expect($directive->getParent()->asDirective()->getCondition())->not->toBeNull();
});

test('basic switch structure tests', function () {
    $template = <<<'EOT'
@switch($something)
    @case(1)
        @lang("something")
       @break;
@endswitch
EOT;
    $doc = $this->getDocument($template)->resolveStructures();
    $directive = $doc->findDirectiveByName('lang');

    expect($directive->hasParentOfType(DirectiveNode::class))->toBeTrue();
    expect($directive->hasParent())->toBeTrue();
    expect($directive->hasSwitchParent())->toBeTrue();
    expect($directive->getParent()->isStructure)->toBeTrue();
    expect($directive->getParent()->asDirective()->getCaseStatement())->not->toBeNull();
    expect($directive->getParent()->asDirective()->getCaseStatement()->getParent()->asDirective()->getSwitchStatement())->not->toBeNull();
});
