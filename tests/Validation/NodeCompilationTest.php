<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Validation\Validators\NodeCompilationValidator;

test('node compilation validator detects issues', function () {
    $template = <<<'BLADE'
    {{ $hello++++ }}
    
    
            {{ $world+++ }}
BLADE;
    $results = Document::fromText($template)
        ->addValidator(new NodeCompilationValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(2);
    expect($results[0]->message)->toBe('Anticipated PHP compilation error: [syntax error, unexpected token "++", expecting ")"] near [{{ $hello++++ }}]');
    expect($results[1]->message)->toBe('Anticipated PHP compilation error: [syntax error, unexpected token ")"] near [{{ $world+++ }}]');
});

test('node compilation with php blocks', function () {
    $template = <<<'BLADE'
@php $count = 1 @endphp
BLADE;
    $results = Document::fromText($template)
        ->addValidator(new NodeCompilationValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(0);
});

test('node compilation validator does not detect issues', function () {
    $template = <<<'BLADE'
    {{ $hello }}
    
    
            {{ $world }}
BLADE;
    $results = Document::fromText($template)
        ->addValidator(new NodeCompilationValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(0);
});
