<?php

namespace Stillat\BladeParser\Tests\Validation;

use Stillat\BladeParser\Compiler\CompilerServices\CoreDirectiveRetriever;
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Tests\ParserTestCase;
use Stillat\BladeParser\Validation\Validators\NoArgumentsValidator;

class NoArgumentsTest extends ParserTestCase
{
    /**
     * @dataProvider directiveNames
     */
    public function testNoArgumentsValidatorDetectsIssues($directiveName)
    {
        $message = "[@{$directiveName}] should not have any arguments";
        $results = Document::fromText("@{$directiveName}(\$some, \$args)")
            ->addValidator(new NoArgumentsValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(1, $results);
        $this->assertSame($message, $results[0]->message);
    }

    /**
     * @dataProvider directiveNames
     */
    public function testNoArgumentsValidatorDoesNotDetectIssues($directiveName)
    {
        $results = Document::fromText("@{$directiveName}")
            ->addValidator(new NoArgumentsValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(0, $results);
    }

    public function directiveNames()
    {
        return collect(CoreDirectiveRetriever::instance()->getDirectivesThatMustNotHaveArguments())->filter(fn ($s) => $s != 'verbatim' && $s != 'endverbatim')->map(fn ($s) => [$s])->values()->all();
    }
}
