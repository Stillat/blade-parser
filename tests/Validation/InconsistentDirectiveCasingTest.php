<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Compiler\CompilerServices\CoreDirectiveRetriever;
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Validation\Validators\InconsistentDirectiveCasingValidator;


test('inconsistent directive casing validator detects issues', function ($directiveName) {
    if ($directiveName == 'endverbatim') {
        $directiveName = 'endVerbatim';
    }

    $inconsistent = mb_strtoupper($directiveName);

    $message = "Inconsistent casing for [@{$inconsistent}]; expecting [@{$directiveName}]";

    $results = Document::fromText("@{$inconsistent}")
        ->addValidator(new InconsistentDirectiveCasingValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(1);
    expect($results[0]->message)->toBe($message);
})->with(directiveNames());

test('inconsistent directive casing validator does not detect issues', function ($directiveName) {
    if ($directiveName == 'endverbatim') {
        $directiveName = 'endVerbatim';
    }

    $results = Document::fromText("@{$directiveName}")
        ->addValidator(new InconsistentDirectiveCasingValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(0);
})->with(directiveNames());
