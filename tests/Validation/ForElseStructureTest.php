<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Validation\Validators\ForElseStructureValidator;


test('for else validator detects too many empty directives', function () {
    $template = <<<'BLADE'
@forelse ($users as $user)

@empty
@empty

@endforelse
BLADE;
    $results = Document::fromText($template)
        ->addValidator(new ForElseStructureValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(1);
    expect($results[0]->message)->toBe('Too many [@empty] directives inside [@forelse]');
});

test('for else validator detects missing empty directives', function () {
    $template = <<<'BLADE'
@forelse ($users as $user)

@endforelse
BLADE;
    $results = Document::fromText($template)
        ->addValidator(new ForElseStructureValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(1);
    expect($results[0]->message)->toBe('Missing [@empty] directive inside [@forelse]');
});

test('for else validator does not detect issues', function () {
    $template = <<<'BLADE'
@forelse ($users as $user)

@empty

@endforelse
BLADE;
    $results = Document::fromText($template)
        ->addValidator(new ForElseStructureValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(0);
});
