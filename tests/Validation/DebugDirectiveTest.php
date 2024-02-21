<?php

namespace Stillat\BladeParser\Tests\Validation;

use Stillat\BladeParser\Compiler\CompilerServices\CoreDirectiveRetriever;
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Tests\ParserTestCase;
use Stillat\BladeParser\Validation\Validators\DebugDirectiveValidator;

class DebugDirectiveTest extends ParserTestCase
{
    /**
     * @dataProvider  debugDirectives
     */
    public function testDebugDirectiveValidatorDetectsIssues(string $directive)
    {
        $template = "Lead @{$directive}(\$arg) Trail";

        $results = Document::fromText($template)
            ->addValidator(new DebugDirectiveValidator)
            ->validate()->getValidationErrors();

        $message = "Debug directive [@{$directive}] detected";

        $this->assertCount(1, $results);
        $this->assertSame($message, $results[0]->message);
    }

    /**
     * @dataProvider nonDebugDirectives
     */
    public function testDebugDirectiveValidatorDoesNotDetectIssues(string $directive)
    {
        $template = "Lead @{$directive}(\$arg) Trail";

        $results = Document::fromText($template)
            ->addValidator(new DebugDirectiveValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(0, $results);
    }

    public static function debugDirectives()
    {
        return collect(CoreDirectiveRetriever::instance()->getDebugDirectiveNames())->map(function ($directive) {
            return [$directive];
        })->values()->all();
    }

    public static function nonDebugDirectives()
    {
        $debug = CoreDirectiveRetriever::instance()->getDebugDirectiveNames();

        return collect(CoreDirectiveRetriever::instance()->getDirectiveNames())->filter(function ($s) use ($debug) {
            return ! in_array($s, $debug);
        })->map(function ($directive) {
            return [$directive];
        })->values()->all();
    }
}
