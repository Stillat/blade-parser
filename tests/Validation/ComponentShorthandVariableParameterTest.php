<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Validation\Validators\ComponentShorthandVariableParameterValidator;

test('component shorthand validator detects issues', function () {
    $template = <<<'BLADE'
<x-profile $:message /> <x-profile :$message="message" />
BLADE;

    $results = Document::fromText($template)
        ->addValidator(new ComponentShorthandVariableParameterValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(2);

    expect($results[0]->message)->toBe('Potential typo in shorthand parameter variable [$:message]; did you mean [:$message]');
    expect($results[1]->message)->toBe('Unexpected value for shorthand parameter variable near [="message"]');
});

test('component shorthand validator does not detect issues', function () {
    $template = <<<'BLADE'
<x-profile :$message /> <x-profile :$message />
BLADE;

    $results = Document::fromText($template)
        ->addValidator(new ComponentShorthandVariableParameterValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(0);
});
