<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Validation\Validators\DuplicateConditionExpressionsValidator;


test('duplicate condition expression validator detects issues', function () {
    $template = <<<'BLADE'
@if ($this == 'that')

@elseif ($this == 'that')

@else

@endif
BLADE;
    $results = Document::fromText($template)
        ->addValidator(new DuplicateConditionExpressionsValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(1);
    expect($results[0]->message)->toBe("Duplicate expression [\$this == 'that'] in [@elseif]");
});

test('duplicate condition expression validator does not detect issues', function () {
    $template = <<<'BLADE'
@if ($this == 'that')

@elseif ($this == 'something-else')

@else

@endif
BLADE;
    $results = Document::fromText($template)
        ->addValidator(new DuplicateConditionExpressionsValidator)
        ->validate()->getValidationErrors();

    expect($results)->toHaveCount(0);
});
