<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Validation\Validators\DirectiveArgumentSpacingValidator;

test('directive argument spacing validator detects issues', function () {
    $template = <<<'BLADE'
@if  ($something)

@endif
BLADE;
    $spacingValidator = new DirectiveArgumentSpacingValidator;
    $spacingValidator->setExpectedSpacing(3);

    $results = Document::fromText($template)
        ->addValidator($spacingValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(1);
    expect($results[0]->message)->toBe('Expected 3 spaces after [@if], but found 2');
});

test('directive argument spacing validator does not detect issues', function () {
    $template = <<<'BLADE'
@if   ($something)

@endif
BLADE;
    $spacingValidator = new DirectiveArgumentSpacingValidator;
    $spacingValidator->setExpectedSpacing(3);

    $results = Document::fromText($template)
        ->addValidator($spacingValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(0);
});
