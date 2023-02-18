<?php

namespace Stillat\BladeParser\Tests\Validation;

use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Tests\ParserTestCase;
use Stillat\BladeParser\Validation\Validators\EmptyConditionValidator;

class EmptyConditionTest extends ParserTestCase
{
    public function testEmptyConditionValidatorDetectsIssues()
    {
        $template = <<<'BLADE'
@if 

@else

@endif
BLADE;
        $results = Document::fromText($template)
            ->addValidator(new EmptyConditionValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(1, $results);
        $this->assertSame('Invalid empty expression for [@if]', $results[0]->message);
    }

    public function testEmptyConditionValidatorDoesNotDetectIssues()
    {
        $template = <<<'BLADE'
@if ($something)

@else

@endif
BLADE;
        $results = Document::fromText($template)
            ->addValidator(new EmptyConditionValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(0, $results);
    }
}
