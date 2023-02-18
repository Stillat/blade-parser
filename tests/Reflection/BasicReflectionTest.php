<?php

namespace Stillat\BladeParser\Tests\Reflection;

use Illuminate\Support\Collection;
use Stillat\BladeParser\Nodes\CommentNode;
use Stillat\BladeParser\Tests\ParserTestCase;

class BasicReflectionTest extends ParserTestCase
{
    public function testFindDirectiveByName()
    {
        $template = <<<'EOT'
@extends('layout')

@section ('section_name')

@endSection
EOT;
        $document = $this->getDocument($template);
        $extends = $document->findDirectiveByName('extends');
        $this->assertNotNull($extends);
        $this->assertTrue($extends->hasArguments());
        $this->assertSame("'layout'", $extends->getValue());
    }

    public function testNestedFindDirectiveByName()
    {
        $template = <<<'EOT'
@extends('layout')

@section ('section_name')
    @include('path');
@endSection
EOT;
        $document = $this->getDocument($template);
        $document->resolveStructures();
        $section = $document->findDirectiveByName('section');
        $this->assertNotNull($section);
        $include = $section->findDirectiveByName('include');
        $this->assertNotNull($include);
        $this->assertSame("'path'", $include->arguments->innerContent);
    }

    public function testHasDirective()
    {
        $template = <<<'EOT'
@extends('layout')

@section ('section_name')

@endSection
EOT;
        $document = $this->getDocument($template);
        $this->assertTrue($document->hasDirective('extends'));
        $this->assertFalse($document->hasDirective('nope'));
    }

    public function testMultipleDirectives()
    {
        $template = <<<'EOT'
@extends('layout')
@extends('layout1')
@extends('layout2')
EOT;
        $document = $this->getDocument($template);
        $extends = $document->findDirectivesByName('extends');
        $this->assertCount(3, $extends);
        $this->assertInstanceOf(Collection::class, $extends);
        $this->assertSame("'layout'", $extends[0]->getValue());
        $this->assertSame("'layout1'", $extends[1]->getValue());
        $this->assertSame("'layout2'", $extends[2]->getValue());
    }

    public function testRetrieveComments()
    {
        $template = <<<'EOT'
{{-- Comment one --}}
literal one
{{-- Comment two --}}
{{-- Comment three --}}{{-- Comment four --}}
EOT;

        $document = $this->getDocument($template);
        $comments = $document->getComments();
        $this->assertCount(4, $comments);
        $this->assertSame(' Comment one ', $comments->first()->innerContent);
        $this->assertSame(' Comment two ', $comments[1]->innerContent);
        $this->assertSame(' Comment three ', $comments[2]->innerContent);
        $this->assertSame(' Comment four ', $comments->last()->innerContent);
    }

    public function testLastOfType()
    {
        $template = <<<'EOT'
{{-- Comment one --}}
literal one
{{-- Comment two --}}
{{-- Comment three --}}{{-- Comment four --}}
EOT;
        $this->assertSame(
            ' Comment four ',
            $this->getDocument($template)->lastOfType(CommentNode::class)->innerContent
        );
    }

    public function testGetNodesBefore()
    {
        $template = <<<'EOT'
D1 @if ('one')
D2 @if ('two')
D3 @if ('three')
D4 @if ('four')
EOT;
        $doc = $this->getDocument($template);
        $allNodes = $doc->getNodes()->all();
        // getLiterals()[2] will map the newline and D3 label.
        $before = $doc->getNodesBefore($doc->getLiterals()[2])->all();

        $expected = [
            $allNodes[0], // L1
            $allNodes[1], // D1
            $allNodes[2], // L2
            $allNodes[3], // D2
        ];

        $this->assertSame($expected, $before);
    }

    public function testGetNodesAfter()
    {
        $template = <<<'EOT'
D1 @if ('one')
D2 @if ('two')
D3 @if ('three')
D4 @if ('four')
EOT;
        $doc = $this->getDocument($template);
        $allNodes = $doc->getNodes()->all();
        // getLiterals()[2] will map the newline and D3 label.
        $after = $doc->getNodesAfter($doc->getLiterals()[2])->all();

        $expected = [
            $allNodes[5], // D3
            $allNodes[6], // L1
            $allNodes[7], // D4
        ];

        $this->assertSame($expected, $after);
    }

    public function testBasicEchoRetrieval()
    {
        $template = <<<'EOT'
One {{ $two }} {!! $three !!}} {{{ $four }}}
EOT;
        $doc = $this->getDocument($template);
        $this->assertCount(3, $doc->getEchoes());
    }

    public function testResolvingDynamicStructureNames()
    {
        $template = <<<'EOT'
@can ($doSomething)


@endcan
EOT;
        $doc = $this->getDocument($template)->resolveStructures();
        $directive = $doc->findDirectiveByName('can');
        $this->assertTrue($directive->getConditionRequiresClose());
        $this->assertTrue($directive->getIsConditionDirective());
        $this->assertSame('if', $directive->getConditionStructureName());

        $directive = $doc->findDirectiveByName('endcan');
        $this->assertFalse($directive->getConditionRequiresClose());
        $this->assertTrue($directive->getIsConditionDirective());
        $this->assertSame('if', $directive->getConditionStructureName());
    }
}
