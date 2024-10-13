<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);

use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Validation\Validators\NoArgumentsValidator;

test('no arguments validator detects issues', function ($directiveName) {
    $message = "[@{$directiveName}] should not have any arguments";
    $results = Document::fromText("@{$directiveName}(\$some, \$args)")
        ->addValidator(new NoArgumentsValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(1);
    expect($results[0]->message)->toBe($message);
})->with(directiveNamesWithoutArguments());

test('no arguments validator does not detect issues', function ($directiveName) {
    $results = Document::fromText("@{$directiveName}")
        ->addValidator(new NoArgumentsValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(0);
})->with(directiveNamesWithoutArguments());
