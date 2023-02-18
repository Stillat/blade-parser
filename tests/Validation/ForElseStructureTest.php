<?php

namespace Stillat\BladeParser\Tests\Validation;

use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Tests\ParserTestCase;
use Stillat\BladeParser\Validation\Validators\ForElseStructureValidator;

class ForElseStructureTest extends ParserTestCase
{
    public function testForElseValidatorDetectsTooManyEmptyDirectives()
    {
        $template = <<<'BLADE'
@forelse ($users as $user)

@empty
@empty

@endforelse
BLADE;
        $results = Document::fromText($template)
            ->addValidator(new ForElseStructureValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(1, $results);
        $this->assertSame('Too many [@empty] directives inside [@forelse]', $results[0]->message);
    }

    public function testForElseValidatorDetectsMissingEmptyDirectives()
    {
        $template = <<<'BLADE'
@forelse ($users as $user)

@endforelse
BLADE;
        $results = Document::fromText($template)
            ->addValidator(new ForElseStructureValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(1, $results);
        $this->assertSame('Missing [@empty] directive inside [@forelse]', $results[0]->message);
    }

    public function testForElseValidatorDoesNotDetectIssues()
    {
        $template = <<<'BLADE'
@forelse ($users as $user)

@empty

@endforelse
BLADE;
        $results = Document::fromText($template)
            ->addValidator(new ForElseStructureValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(0, $results);
    }
}
