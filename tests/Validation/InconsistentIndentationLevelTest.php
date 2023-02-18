<?php

namespace Stillat\BladeParser\Tests\Validation;

use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Tests\ParserTestCase;
use Stillat\BladeParser\Validation\Validators\InconsistentIndentationLevelValidator;

class InconsistentIndentationLevelTest extends ParserTestCase
{
    public function testInconsistentIndentationLevelDetectsIssues()
    {
        $template = <<<'BLADE'
@if ($this)

        @endif
BLADE;
        $results = Document::fromText($template)
            ->addValidator(new InconsistentIndentationLevelValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(1, $results);
        $this->assertSame('Inconsistent indentation level of 8 for [@endif]; parent [@if] has a level of 0', $results[0]->message);
    }

    public function testInconsistentIndentationLevelDoesNotDetectIssues()
    {
        $template = <<<'BLADE'
        @if ($this)

        @endif
        
            @if ($this)
    
            @endif
BLADE;
        $results = Document::fromText($template)
            ->addValidator(new InconsistentIndentationLevelValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(0, $results);
    }
}
