<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Validation\Validators\RequiredArgumentsValidator;

test('required arguments validator detects issues', function ($directiveName) {
    $message = "Required arguments missing for [@{$directiveName}]";

    $results = Document::fromText("@{$directiveName}")
        ->addValidator(new RequiredArgumentsValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(1);
    expect($results[0]->message)->toBe($message);
})->with(directiveNamesRequiringArguments());

test('required arguments validator does not detect issues', function ($directiveName) {
    $results = Document::fromText("@{$directiveName}(\$args)")
        ->addValidator(new RequiredArgumentsValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(0);
})->with(directiveNamesRequiringArguments());
