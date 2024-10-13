<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Validation\Validators\DirectiveArgumentsSpanningLinesValidator;


test('directive argument spanning lines validator detects issues', function () {
    $template = <<<'BLADE'
@if ($something
    == $this &&
    'this' == 'that')
BLADE;
    $spanLinesValidator = new DirectiveArgumentsSpanningLinesValidator();
    $spanLinesValidator->setMaxLineSpan(2);

    $results = Document::fromText($template)
        ->addValidator($spanLinesValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(1);
    expect($results[0]->message)->toBe('Maximum line count exceeded for [@if] arguments; found 3 lines expecting a maximum of 2 lines');
});

test('directive argument spanning lines validator does not detect issues', function () {
    $template = <<<'BLADE'
@if ($something
    == $this &&
    'this' == 'that')
BLADE;
    $spanLinesValidator = new DirectiveArgumentsSpanningLinesValidator();
    $spanLinesValidator->setMaxLineSpan(3);

    $results = Document::fromText($template)
        ->addValidator($spanLinesValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(0);
});

test('directive argument spanning lines validator does not detect issues when below line count', function () {
    $template = <<<'BLADE'
@if ($something
    'this' == 'that')
BLADE;
    $spanLinesValidator = new DirectiveArgumentsSpanningLinesValidator();
    $spanLinesValidator->setMaxLineSpan(3);

    $results = Document::fromText($template)
        ->addValidator($spanLinesValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(0);
});
