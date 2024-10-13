<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Validation\Validators\ComponentParameterNameSpacingValidator;

test('parameter spacing is detected', function () {
    $template = <<<'BLADE'
<x-alert message = "The message" />
BLADE;
    $results = Document::fromText($template)
        ->addValidator(new ComponentParameterNameSpacingValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(1);
    expect($results[0]->message)->toBe('Invalid spacing between component parameter name/value near [message]');
});

test('parameter spacing doesnt detect issues', function () {
    $template = <<<'BLADE'
<x-alert message="The message" />
BLADE;
    $results = Document::fromText($template)
        ->addValidator(new ComponentParameterNameSpacingValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(0);
});
