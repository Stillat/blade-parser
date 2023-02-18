<?php

namespace Stillat\BladeParser\Tests\Validation;

use Stillat\BladeParser\Compiler\CompilerServices\CoreDirectiveRetriever;
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Tests\ParserTestCase;
use Stillat\BladeParser\Validation\Validators\RequiredArgumentsValidator;

class RequiredArgumentsTest extends ParserTestCase
{
    /**
     * @dataProvider directiveNames
     */
    public function testRequiredArgumentsValidatorDetectsIssues($directiveName)
    {
        $message = "Required arguments missing for [@{$directiveName}]";

        $results = Document::fromText("@{$directiveName}")
            ->addValidator(new RequiredArgumentsValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(1, $results);
        $this->assertSame($message, $results[0]->message);
    }

    /**
     * @dataProvider directiveNames
     */
    public function testRequiredArgumentsValidatorDoesNotDetectIssues($directiveName)
    {
        $results = Document::fromText("@{$directiveName}(\$args)")
            ->addValidator(new RequiredArgumentsValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(0, $results);
    }

    public function directiveNames()
    {
        return collect(CoreDirectiveRetriever::instance()->getDirectivesRequiringArguments())->map(fn ($s) => [$s])->values()->all();
    }
}
