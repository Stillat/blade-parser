<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Nodes\DirectiveNode;

test('spanned line count', function () {
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
    expect($doc->hasAnyDirectives())->toBeTrue();

    /** @var DirectiveNode[] $ifs */
    $ifs = $doc->findDirectivesByName('if');

    expect($ifs[0]->getSpannedLineCount())->toBe(1);
    expect($ifs[0]->argumentsBeginOnSameLine())->toBeTrue();
    expect($ifs[0]->spansMultipleLines())->toBeFalse();
    expect($ifs[1]->getSpannedLineCount())->toBe(4);
    expect($ifs[1]->spansMultipleLines())->toBeTrue();
    expect($ifs[1]->argumentsBeginOnSameLine())->toBeFalse();
});

test('invalid spanned line count', function () {
    $template = <<<'EOT'
@if ($something == 

                'something')

EOT;
    $doc = $this->getDocument($template);
    expect($doc->hasAnyDirectives())->toBeTrue();

    /** @var DirectiveNode[] $ifs */
    $ifs = $doc->findDirectivesByName('if');
    $ifs[0]->position = null;
    expect($ifs[0]->getSpannedLineCount())->toBe(1);
});

test('arg distance', function () {
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

    expect($ifs[0]->getArgumentsDistance())->toBe(5);
    expect($ifs[1]->getArgumentsDistance())->toBe(1);
    expect($ifs[2]->getArgumentsDistance())->toBe(0);
});

test('directive argument splitting', function () {
    $template = <<<'BLADE'
@extends("layout")

@section("content")
  @lang("arg1")
@endsection
BLADE;
    $args = Document::fromText($template)->findDirectiveByName('lang')->arguments->getValues();

    expect($args)->toHaveCount(1);
    expect($args[0])->toBe('"arg1"');
});

test('directive argument splitting with json content', function () {
    $template = <<<'BLADE'
@extends("layout")

@section("content")
  @lang({"some": "content", "more": "content"})
@endsection
BLADE;
    $args = Document::fromText($template)->findDirectiveByName('lang')->arguments->getValues();

    expect($args)->toHaveCount(1);
    expect($args[0])->toBe('{"some": "content", "more": "content"}');
});

test('directive argument splitting with multiple arguments', function () {
    $template = <<<'BLADE'
@extends("layout")

@section("content")
  @lang("arg1", 'arg2', [1,2,3,4,5], ([1,2,3,4), "string,with,commas")
@endsection
BLADE;
    $args = Document::fromText($template)->findDirectiveByName('lang')->arguments->getValues();

    expect($args)->toHaveCount(5);
    expect($args[0])->toBe('"arg1"');
    expect($args[1])->toBe("'arg2'");
    expect($args[2])->toBe('[1,2,3,4,5]');
    expect($args[3])->toBe('([1,2,3,4)');
    expect($args[4])->toBe('"string,with,commas"');
});

test('directive argument splitting with empty string', function () {
    $template = <<<'BLADE'
@extends("layout")

@section("content")
  @lang()
@endsection
BLADE;
    $args = Document::fromText($template)->findDirectiveByName('lang')->arguments->getValues();

    expect($args)->toHaveCount(0);
});
