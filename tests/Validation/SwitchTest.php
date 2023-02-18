<?php

namespace Stillat\BladeParser\Tests\Validation;

use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Tests\ParserTestCase;
use Stillat\BladeParser\Validation\Validators\SwitchValidator;

class SwitchTest extends ParserTestCase
{
    public function testSwitchValidatorDetectsNoCaseStatements()
    {
        $template = <<<'BLADE'
@switch ($var)

@endswitch
BLADE;
        $results = Document::fromText($template)
            ->addValidator(new SwitchValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(1, $results);
        $this->assertSame('No case statements found in [@switch]', $results[0]->message);
    }

    public function testSwitchValidatorDetectsMissingBreakStatements()
    {
        $template = <<<'BLADE'
@switch ($var)
    @case (1)
        Something
@endswitch
BLADE;
        $results = Document::fromText($template)
            ->addValidator(new SwitchValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(1, $results);
        $this->assertSame('Missing [@break] statement inside [@case]', $results[0]->message);
    }

    public function testSwitchValidatorDetectsTooManyBreakStatements()
    {
        $template = <<<'BLADE'
@switch ($var)
    @case (1)
        Something
        @break
        @break
@endswitch
BLADE;
        $results = Document::fromText($template)
            ->addValidator(new SwitchValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(1, $results);
        $this->assertSame('Too many [@break] statements inside [@case]', $results[0]->message);
    }

    public function testSwitchValidatorDetectsTooManyDefaultCases()
    {
        $template = <<<'BLADE'
@switch ($var)
    @case (1)
        Something
        @break
    @default
        One
        @break
    @default
        Two
        @break
@endswitch
BLADE;
        $results = Document::fromText($template)
            ->addValidator(new SwitchValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(1, $results);
        $this->assertSame('Too many [@default] cases in [@switch]', $results[0]->message);
    }

    public function testSwitchValidatorDoesNotDetectIssues()
    {
        $template = <<<'BLADE'
@switch ($var)
    @case (1)
        Something
        @break
    @default
        Something
        @break
@endswitch
BLADE;
        $results = Document::fromText($template)
            ->addValidator(new SwitchValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(0, $results);
    }
}
