<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Validation\Validators\DirectiveSpacingValidator;

test('directive spacing validator detects issues', function () {
    $template = 'class="@if @endif"';
    $results = Document::fromText($template)
        ->addValidator(new DirectiveSpacingValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(2);
    expect($results[0]->message)->toBe('Missing space before [@if]');
    expect($results[1]->message)->toBe('Missing space after [@endif]');
});

test('directive spacing validator does not detect issues', function () {
    $template = 'class=" @if @endif "';
    $results = Document::fromText($template)
        ->addValidator(new DirectiveSpacingValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(0);
});
