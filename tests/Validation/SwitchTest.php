<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Validation\Validators\SwitchValidator;

test('switch validator detects no case statements', function () {
    $template = <<<'BLADE'
@switch ($var)

@endswitch
BLADE;
    $results = Document::fromText($template)
        ->addValidator(new SwitchValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(1);
    expect($results[0]->message)->toBe('No case statements found in [@switch]');
});

test('switch validator detects missing break statements', function () {
    $template = <<<'BLADE'
@switch ($var)
    @case (1)
        Something
@endswitch
BLADE;
    $results = Document::fromText($template)
        ->addValidator(new SwitchValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(1);
    expect($results[0]->message)->toBe('Missing [@break] statement inside [@case]');
});

test('switch validator detects too many break statements', function () {
    $template = <<<'BLADE'
@switch ($var)
    @case (1)
        Something
        @break
        @break
@endswitch
BLADE;
    $results = Document::fromText($template)
        ->addValidator(new SwitchValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(1);
    expect($results[0]->message)->toBe('Too many [@break] statements inside [@case]');
});

test('switch validator detects too many default cases', function () {
    $template = <<<'BLADE'
@switch ($var)
    @case (1)
        Something
        @break
    @default
        One
        @break
    @default
        Two
        @break
@endswitch
BLADE;
    $results = Document::fromText($template)
        ->addValidator(new SwitchValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(1);
    expect($results[0]->message)->toBe('Too many [@default] cases in [@switch]');
});

test('switch validator does not detect issues', function () {
    $template = <<<'BLADE'
@switch ($var)
    @case (1)
        Something
        @break
    @default
        Something
        @break
@endswitch
BLADE;
    $results = Document::fromText($template)
        ->addValidator(new SwitchValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(0);
});
