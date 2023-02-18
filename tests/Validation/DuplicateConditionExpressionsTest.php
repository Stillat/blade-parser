<?php

namespace Stillat\BladeParser\Tests\Validation;

use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Tests\ParserTestCase;
use Stillat\BladeParser\Validation\Validators\DuplicateConditionExpressionsValidator;

class DuplicateConditionExpressionsTest extends ParserTestCase
{
    public function testDuplicateConditionExpressionValidatorDetectsIssues()
    {
        $template = <<<'BLADE'
@if ($this == 'that')

@elseif ($this == 'that')

@else

@endif
BLADE;
        $results = Document::fromText($template)
            ->addValidator(new DuplicateConditionExpressionsValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(1, $results);
        $this->assertSame("Duplicate expression [\$this == 'that'] in [@elseif]", $results[0]->message);
    }

    public function testDuplicateConditionExpressionValidatorDoesNotDetectIssues()
    {
        $template = <<<'BLADE'
@if ($this == 'that')

@elseif ($this == 'something-else')

@else

@endif
BLADE;
        $results = Document::fromText($template)
            ->addValidator(new DuplicateConditionExpressionsValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(0, $results);
    }
}
