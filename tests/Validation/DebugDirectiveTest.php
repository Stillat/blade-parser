<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Compiler\CompilerServices\CoreDirectiveRetriever;
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Validation\Validators\DebugDirectiveValidator;

test('debug directive validator detects issues', function (string $directive) {
    $template = "Lead @{$directive}(\$arg) Trail";

    $results = Document::fromText($template)
        ->addValidator(new DebugDirectiveValidator)
        ->validate()->getValidationErrors();

    $message = "Debug directive [@{$directive}] detected";

    expect($results)->toHaveCount(1);
    expect($results[0]->message)->toBe($message);
})->with(debugDirectives());

test('debug directive validator does not detect issues', function (string $directive) {
    $template = "Lead @{$directive}(\$arg) Trail";

    $results = Document::fromText($template)
        ->addValidator(new DebugDirectiveValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(0);
})->with(nonDebugDirectives());

function debugDirectives(): array
{
    return collect(CoreDirectiveRetriever::instance()->getDebugDirectiveNames())->map(function ($directive) {
        return [$directive];
    })->values()->all();
}

function nonDebugDirectives(): array
{
    $debug = CoreDirectiveRetriever::instance()->getDebugDirectiveNames();

    return collect(CoreDirectiveRetriever::instance()->getDirectiveNames())->filter(function ($s) use ($debug) {
        return ! in_array($s, $debug);
    })->map(function ($directive) {
        return [$directive];
    })->values()->all();
}
