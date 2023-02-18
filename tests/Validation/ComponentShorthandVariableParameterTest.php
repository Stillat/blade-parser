<?php

namespace Stillat\BladeParser\Tests\Validation;

use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Tests\ParserTestCase;
use Stillat\BladeParser\Validation\Validators\ComponentShorthandVariableParameterValidator;

class ComponentShorthandVariableParameterTest extends ParserTestCase
{
    public function testComponentShorthandValidatorDetectsIssues()
    {
        $template = <<<'BLADE'
<x-profile $:message /> <x-profile :$message="message" />
BLADE;

        $results = Document::fromText($template)
            ->addValidator(new ComponentShorthandVariableParameterValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(2, $results);

        $this->assertSame('Potential typo in shorthand parameter variable [$:message]; did you mean [:$message]', $results[0]->message);
        $this->assertSame('Unexpected value for shorthand parameter variable near [="message"]', $results[1]->message);
    }

    public function testComponentShorthandValidatorDoesNotDetectIssues()
    {
        $template = <<<'BLADE'
<x-profile :$message /> <x-profile :$message />
BLADE;

        $results = Document::fromText($template)
            ->addValidator(new ComponentShorthandVariableParameterValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(0, $results);
    }
}
