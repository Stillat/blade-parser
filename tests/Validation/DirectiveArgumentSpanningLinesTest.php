<?php

namespace Stillat\BladeParser\Tests\Validation;

use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Tests\ParserTestCase;
use Stillat\BladeParser\Validation\Validators\DirectiveArgumentsSpanningLinesValidator;

class DirectiveArgumentSpanningLinesTest extends ParserTestCase
{
    public function testDirectiveArgumentSpanningLinesValidatorDetectsIssues()
    {
        $template = <<<'BLADE'
@if ($something
    == $this &&
    'this' == 'that')
BLADE;
        $spanLinesValidator = new DirectiveArgumentsSpanningLinesValidator();
        $spanLinesValidator->setMaxLineSpan(2);

        $results = Document::fromText($template)
            ->addValidator($spanLinesValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(1, $results);
        $this->assertSame('Maximum line count exceeded for [@if] arguments; found 3 lines expecting a maximum of 2 lines', $results[0]->message);
    }

    public function testDirectiveArgumentSpanningLinesValidatorDoesNotDetectIssues()
    {
        $template = <<<'BLADE'
@if ($something
    == $this &&
    'this' == 'that')
BLADE;
        $spanLinesValidator = new DirectiveArgumentsSpanningLinesValidator();
        $spanLinesValidator->setMaxLineSpan(3);

        $results = Document::fromText($template)
            ->addValidator($spanLinesValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(0, $results);
    }

    public function testDirectiveArgumentSpanningLinesValidatorDoesNotDetectIssuesWhenBelowLineCount()
    {
        $template = <<<'BLADE'
@if ($something
    'this' == 'that')
BLADE;
        $spanLinesValidator = new DirectiveArgumentsSpanningLinesValidator();
        $spanLinesValidator->setMaxLineSpan(3);

        $results = Document::fromText($template)
            ->addValidator($spanLinesValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(0, $results);
    }
}
