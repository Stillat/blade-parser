<?php

namespace Stillat\BladeParser\Tests\Validation;

use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Tests\ParserTestCase;
use Stillat\BladeParser\Validation\Validators\ComponentParameterNameSpacingValidator;

class ComponentParameterNameSpacingTest extends ParserTestCase
{
    public function testParameterSpacingIsDetected()
    {
        $template = <<<'BLADE'
<x-alert message = "The message" />
BLADE;
        $results = Document::fromText($template)
            ->addValidator(new ComponentParameterNameSpacingValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(1, $results);
        $this->assertSame('Invalid spacing between component parameter name/value near [message]', $results[0]->message);
    }

    public function testParameterSpacingDoesntDetectIssues()
    {
        $template = <<<'BLADE'
<x-alert message="The message" />
BLADE;
        $results = Document::fromText($template)
            ->addValidator(new ComponentParameterNameSpacingValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(0, $results);
    }
}
