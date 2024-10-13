<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Validation\Validators\InconsistentIndentationLevelValidator;

test('inconsistent indentation level detects issues', function () {
    $template = <<<'BLADE'
@if ($this)

        @endif
BLADE;
    $results = Document::fromText($template)
        ->addValidator(new InconsistentIndentationLevelValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(1);
    expect($results[0]->message)->toBe('Inconsistent indentation level of 8 for [@endif]; parent [@if] has a level of 0');
});

test('inconsistent indentation level does not detect issues', function () {
    $template = <<<'BLADE'
        @if ($this)

        @endif
        
            @if ($this)
    
            @endif
BLADE;
    $results = Document::fromText($template)
        ->addValidator(new InconsistentIndentationLevelValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(0);
});
