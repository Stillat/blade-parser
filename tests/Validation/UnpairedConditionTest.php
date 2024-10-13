<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Validation\Validators\UnpairedConditionValidator;


test('unpaired condition validator detects issues', function () {
    $template = <<<'BLADE'
@if ($this)

@elseif ($that)
BLADE;
    $results = Document::fromText($template)
        ->addValidator(new UnpairedConditionValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(2);
    expect($results[0]->message)->toBe('Unpaired condition [@if]');
    expect($results[1]->message)->toBe('Unpaired condition [@elseif]');
});

test('unpaired condition validator does not detect issues', function () {
    $template = <<<'BLADE'
@if ($this)

@elseif ($that)

@else

@endif
BLADE;
    $results = Document::fromText($template)
        ->addValidator(new UnpairedConditionValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(0);
});
