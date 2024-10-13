<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Illuminate\Support\Collection;
use Stillat\BladeParser\Nodes\CommentNode;

test('find directive by name', function () {
    $template = <<<'EOT'
@extends('layout')

@section ('section_name')

@endSection
EOT;
    $document = $this->getDocument($template);
    $extends = $document->findDirectiveByName('extends');
    expect($extends)->not->toBeNull();
    expect($extends->hasArguments())->toBeTrue();
    expect($extends->getValue())->toBe("'layout'");
});

test('nested find directive by name', function () {
    $template = <<<'EOT'
@extends('layout')

@section ('section_name')
    @include('path');
@endSection
EOT;
    $document = $this->getDocument($template);
    $document->resolveStructures();
    $section = $document->findDirectiveByName('section');
    expect($section)->not->toBeNull();
    $include = $section->findDirectiveByName('include');
    expect($include)->not->toBeNull();
    expect($include->arguments->innerContent)->toBe("'path'");
});

test('has directive', function () {
    $template = <<<'EOT'
@extends('layout')

@section ('section_name')

@endSection
EOT;
    $document = $this->getDocument($template);
    expect($document->hasDirective('extends'))->toBeTrue();
    expect($document->hasDirective('nope'))->toBeFalse();
});

test('multiple directives', function () {
    $template = <<<'EOT'
@extends('layout')
@extends('layout1')
@extends('layout2')
EOT;
    $document = $this->getDocument($template);
    $extends = $document->findDirectivesByName('extends');
    expect($extends)->toHaveCount(3);
    expect($extends)->toBeInstanceOf(Collection::class);
    expect($extends[0]->getValue())->toBe("'layout'");
    expect($extends[1]->getValue())->toBe("'layout1'");
    expect($extends[2]->getValue())->toBe("'layout2'");
});

test('retrieve comments', function () {
    $template = <<<'EOT'
{{-- Comment one --}}
literal one
{{-- Comment two --}}
{{-- Comment three --}}{{-- Comment four --}}
EOT;

    $document = $this->getDocument($template);
    $comments = $document->getComments();
    expect($comments)->toHaveCount(4);
    expect($comments->first()->innerContent)->toBe(' Comment one ');
    expect($comments[1]->innerContent)->toBe(' Comment two ');
    expect($comments[2]->innerContent)->toBe(' Comment three ');
    expect($comments->last()->innerContent)->toBe(' Comment four ');
});

test('last of type', function () {
    $template = <<<'EOT'
{{-- Comment one --}}
literal one
{{-- Comment two --}}
{{-- Comment three --}}{{-- Comment four --}}
EOT;
    expect($this->getDocument($template)->lastOfType(CommentNode::class)->innerContent)->toBe(' Comment four ');
});

test('get nodes before', function () {
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

    expect($before)->toBe($expected);
});

test('get nodes after', function () {
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

    expect($after)->toBe($expected);
});

test('basic echo retrieval', function () {
    $template = <<<'EOT'
One {{ $two }} {!! $three !!}} {{{ $four }}}
EOT;
    $doc = $this->getDocument($template);
    expect($doc->getEchoes())->toHaveCount(3);
});

test('resolving dynamic structure names', function () {
    $template = <<<'EOT'
@can ($doSomething)


@endcan
EOT;
    $doc = $this->getDocument($template)->resolveStructures();
    $directive = $doc->findDirectiveByName('can');
    expect($directive->getConditionRequiresClose())->toBeTrue();
    expect($directive->getIsConditionDirective())->toBeTrue();
    expect($directive->getConditionStructureName())->toBe('if');

    $directive = $doc->findDirectiveByName('endcan');
    expect($directive->getConditionRequiresClose())->toBeFalse();
    expect($directive->getIsConditionDirective())->toBeTrue();
    expect($directive->getConditionStructureName())->toBe('if');
});
