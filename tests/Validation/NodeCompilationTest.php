<?php

namespace Stillat\BladeParser\Tests\Validation;

use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Tests\ParserTestCase;
use Stillat\BladeParser\Validation\Validators\NodeCompilationValidator;

class NodeCompilationTest extends ParserTestCase
{
    public function testNodeCompilationValidatorDetectsIssues()
    {
        $template = <<<'BLADE'
    {{ $hello++++ }}
    
    
            {{ $world+++ }}
BLADE;
        $results = Document::fromText($template)
            ->addValidator(new NodeCompilationValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(2, $results);
        $this->assertSame('Anticipated PHP compilation error: [syntax error, unexpected token "++", expecting ")"] near [{{ $hello++++ }}]', $results[0]->message);
        $this->assertSame('Anticipated PHP compilation error: [syntax error, unexpected token ")"] near [{{ $world+++ }}]', $results[1]->message);
    }

    public function testNodeCompilationWithPhpBlocks()
    {
        $template = <<<'BLADE'
@php $count = 1 @endphp
BLADE;
        $results = Document::fromText($template)
            ->addValidator(new NodeCompilationValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(0, $results);
    }

    public function testNodeCompilationValidatorDoesNotDetectIssues()
    {
        $template = <<<'BLADE'
    {{ $hello }}
    
    
            {{ $world }}
BLADE;
        $results = Document::fromText($template)
            ->addValidator(new NodeCompilationValidator)
            ->validate()->getValidationErrors();

        $this->assertCount(0, $results);
    }
}
