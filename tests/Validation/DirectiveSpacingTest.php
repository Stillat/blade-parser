<?php

namespace Stillat\BladeParser\Tests\Validation;

use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Tests\ParserTestCase;
use Stillat\BladeParser\Validation\Validators\DirectiveSpacingValidator;

class DirectiveSpacingTest extends ParserTestCase
{
    public function testDirectiveSpacingValidatorDetectsIssues()
    {
        $template = 'class="@if @endif"';
        $results = Document::fromText($template)
            ->addValidator(new DirectiveSpacingValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(2, $results);
        $this->assertSame('Missing space before [@if]', $results[0]->message);
        $this->assertSame('Missing space after [@endif]', $results[1]->message);
    }

    public function testDirectiveSpacingValidatorDoesNotDetectIssues()
    {
        $template = 'class=" @if @endif "';
        $results = Document::fromText($template)
            ->addValidator(new DirectiveSpacingValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(0, $results);
    }
}
