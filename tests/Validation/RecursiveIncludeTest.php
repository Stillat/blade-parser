<?php

namespace Stillat\BladeParser\Tests\Validation;

use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Tests\ParserTestCase;
use Stillat\BladeParser\Validation\Validators\RecursiveIncludeValidator;

class RecursiveIncludeTest extends ParserTestCase
{
    public function testRecursiveIncludeDetectsIssues()
    {
        $template = <<<'BLADE'
@include('/tmp/file')
BLADE;
        $results = Document::fromText($template, filePath: '/tmp/file.blade.php')
            ->addValidator(new RecursiveIncludeValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(1, $results);
        $this->assertSame("Possible infinite recursion detected near [@include('/tmp/file')]", $results[0]->message);
    }

    public function testRecursiveIncludeDoesNotDetectIssues()
    {
        $template = <<<'BLADE'
@if ($someCondition)
    @include('/tmp/file')
@endif
BLADE;
        $results = Document::fromText($template, filePath: '/tmp/file.blade.php')
            ->addValidator(new RecursiveIncludeValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(0, $results);
    }
}
