<?php

namespace Stillat\BladeParser\Tests\Reflection;

use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Tests\ParserTestCase;

class DirectiveDetailsTest extends ParserTestCase
{
    public function testSpannedLineCount()
    {
        $template = <<<'EOT'
@if ($something == 'something')

@elseif

@if 
    ($something
    == 
        'something')
        
@endif
EOT;
        $doc = $this->getDocument($template);
        $this->assertTrue($doc->hasAnyDirectives());
        /** @var DirectiveNode[] $ifs */
        $ifs = $doc->findDirectivesByName('if');

        $this->assertSame(1, $ifs[0]->getSpannedLineCount());
        $this->assertTrue($ifs[0]->argumentsBeginOnSameLine());
        $this->assertFalse($ifs[0]->spansMultipleLines());
        $this->assertSame(4, $ifs[1]->getSpannedLineCount());
        $this->assertTrue($ifs[1]->spansMultipleLines());
        $this->assertFalse($ifs[1]->argumentsBeginOnSameLine());
    }

    public function testInvalidSpannedLineCount()
    {
        $template = <<<'EOT'
@if ($something == 

                'something')

EOT;
        $doc = $this->getDocument($template);
        $this->assertTrue($doc->hasAnyDirectives());
        /** @var DirectiveNode[] $ifs */
        $ifs = $doc->findDirectivesByName('if');
        $ifs[0]->position = null;
        $this->assertSame(1, $ifs[0]->getSpannedLineCount());
    }

    public function testArgDistance()
    {
        $template = <<<'EOT'
@if     ($something == 'something')

@elseif

@if ($something == 'something')
        
@endif

@if($something == 'something')
        
@endif
EOT;
        $doc = $this->getDocument($template);
        /** @var DirectiveNode[] $ifs */
        $ifs = $doc->findDirectivesByName('if');

        $this->assertSame(5, $ifs[0]->getArgumentsDistance());
        $this->assertSame(1, $ifs[1]->getArgumentsDistance());
        $this->assertSame(0, $ifs[2]->getArgumentsDistance());
    }

    public function testDirectiveArgumentSplitting()
    {
        $template = <<<'BLADE'
@extends("layout")

@section("content")
  @lang("arg1")
@endsection
BLADE;
        $args = Document::fromText($template)->findDirectiveByName('lang')->arguments->getValues();

        $this->assertCount(1, $args);
        $this->assertSame('"arg1"', $args[0]);
    }

    public function testDirectiveArgumentSplittingWithJsonContent()
    {
        $template = <<<'BLADE'
@extends("layout")

@section("content")
  @lang({"some": "content", "more": "content"})
@endsection
BLADE;
        $args = Document::fromText($template)->findDirectiveByName('lang')->arguments->getValues();

        $this->assertCount(1, $args);
        $this->assertSame('{"some": "content", "more": "content"}', $args[0]);
    }

    public function testDirectiveArgumentSplittingWithMultipleArguments()
    {
        $template = <<<'BLADE'
@extends("layout")

@section("content")
  @lang("arg1", 'arg2', [1,2,3,4,5], ([1,2,3,4), "string,with,commas")
@endsection
BLADE;
        $args = Document::fromText($template)->findDirectiveByName('lang')->arguments->getValues();

        $this->assertCount(5, $args);
        $this->assertSame('"arg1"', $args[0]);
        $this->assertSame("'arg2'", $args[1]);
        $this->assertSame('[1,2,3,4,5]', $args[2]);
        $this->assertSame('([1,2,3,4)', $args[3]);
        $this->assertSame('"string,with,commas"', $args[4]);
    }

    public function testDirectiveArgumentSplittingWithEmptyString()
    {
        $template = <<<'BLADE'
@extends("layout")

@section("content")
  @lang()
@endsection
BLADE;
        $args = Document::fromText($template)->findDirectiveByName('lang')->arguments->getValues();

        $this->assertCount(0, $args);
    }
}
