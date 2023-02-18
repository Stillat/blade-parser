<?php

namespace Stillat\BladeParser\Tests\Validation;

use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Tests\ParserTestCase;
use Stillat\BladeParser\Validation\Validators\DirectiveArgumentSpacingValidator;

class DirectiveArgumentSpacingTest extends ParserTestCase
{
    public function testDirectiveArgumentSpacingValidatorDetectsIssues()
    {
        $template = <<<'BLADE'
@if  ($something)

@endif
BLADE;
        $spacingValidator = new DirectiveArgumentSpacingValidator();
        $spacingValidator->setExpectedSpacing(3);

        $results = Document::fromText($template)
            ->addValidator($spacingValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(1, $results);
        $this->assertSame('Expected 3 spaces after [@if], but found 2', $results[0]->message);
    }

    public function testDirectiveArgumentSpacingValidatorDoesNotDetectIssues()
    {
        $template = <<<'BLADE'
@if   ($something)

@endif
BLADE;
        $spacingValidator = new DirectiveArgumentSpacingValidator();
        $spacingValidator->setExpectedSpacing(3);

        $results = Document::fromText($template)
            ->addValidator($spacingValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(0, $results);
    }
}
