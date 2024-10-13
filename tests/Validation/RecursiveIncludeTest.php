<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Validation\Validators\RecursiveIncludeValidator;


test('recursive include detects issues', function () {
    $template = <<<'BLADE'
@include('/tmp/file')
BLADE;
    $results = Document::fromText($template, filePath: '/tmp/file.blade.php')
        ->addValidator(new RecursiveIncludeValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(1);
    expect($results[0]->message)->toBe("Possible infinite recursion detected near [@include('/tmp/file')]");
});

test('recursive include does not detect issues', function () {
    $template = <<<'BLADE'
@if ($someCondition)
    @include('/tmp/file')
@endif
BLADE;
    $results = Document::fromText($template, filePath: '/tmp/file.blade.php')
        ->addValidator(new RecursiveIncludeValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(0);
});
