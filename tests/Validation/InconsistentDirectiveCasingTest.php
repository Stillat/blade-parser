<?php

namespace Stillat\BladeParser\Tests\Validation;

use Stillat\BladeParser\Compiler\CompilerServices\CoreDirectiveRetriever;
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Tests\ParserTestCase;
use Stillat\BladeParser\Validation\Validators\InconsistentDirectiveCasingValidator;

class InconsistentDirectiveCasingTest extends ParserTestCase
{
    /**
     * @dataProvider directiveNames
     */
    public function testInconsistentDirectiveCasingValidatorDetectsIssues($directiveName)
    {
        if ($directiveName == 'endverbatim') {
            $directiveName = 'endVerbatim';
        }

        $inconsistent = mb_strtoupper($directiveName);

        $message = "Inconsistent casing for [@{$inconsistent}]; expecting [@{$directiveName}]";

        $results = Document::fromText("@{$inconsistent}")
            ->addValidator(new InconsistentDirectiveCasingValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(1, $results);
        $this->assertSame($message, $results[0]->message);
    }

    /**
     * @dataProvider directiveNames
     */
    public function testInconsistentDirectiveCasingValidatorDoesNotDetectIssues($directiveName)
    {
        if ($directiveName == 'endverbatim') {
            $directiveName = 'endVerbatim';
        }

        $results = Document::fromText("@{$directiveName}")
            ->addValidator(new InconsistentDirectiveCasingValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(0, $results);
    }

    public static function directiveNames()
    {
        return collect(CoreDirectiveRetriever::instance()->getDirectiveNames())->map(function ($s) {
            return [$s];
        })->values()->all();
    }
}
