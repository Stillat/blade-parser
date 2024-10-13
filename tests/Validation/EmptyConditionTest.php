<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Validation\Validators\EmptyConditionValidator;

test('empty condition validator detects issues', function () {
    $template = <<<'BLADE'
@if 

@else

@endif
BLADE;
    $results = Document::fromText($template)
        ->addValidator(new EmptyConditionValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(1);
    expect($results[0]->message)->toBe('Invalid empty expression for [@if]');
});

test('empty condition validator does not detect issues', function () {
    $template = <<<'BLADE'
@if ($something)

@else

@endif
BLADE;
    $results = Document::fromText($template)
        ->addValidator(new EmptyConditionValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(0);
});
