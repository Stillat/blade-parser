<?php

namespace Stillat\BladeParser\Tests\Validation;

use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Tests\ParserTestCase;
use Stillat\BladeParser\Validation\Validators\UnpairedConditionValidator;

class UnpairedConditionTest extends ParserTestCase
{
    public function testUnpairedConditionValidatorDetectsIssues()
    {
        $template = <<<'BLADE'
@if ($this)

@elseif ($that)
BLADE;
        $results = Document::fromText($template)
            ->addValidator(new UnpairedConditionValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(2, $results);
        $this->assertSame('Unpaired condition [@if]', $results[0]->message);
        $this->assertSame('Unpaired condition [@elseif]', $results[1]->message);
    }

    public function testUnpairedConditionValidatorDoesNotDetectIssues()
    {
        $template = <<<'BLADE'
@if ($this)

@elseif ($that)

@else

@endif
BLADE;
        $results = Document::fromText($template)
            ->addValidator(new UnpairedConditionValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(0, $results);
    }
}
