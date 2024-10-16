<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Compiler\CompilerServices\AttributeCompiler;
use Stillat\BladeParser\Parser\DocumentParser;

beforeEach(function () {
    $this->attributeCompiler = new AttributeCompiler;
    $template = <<<'TEMAPLTE'
<t:component
  parameter="content"
  :binding="$theVariable"
  :$shortHand
  ::escaped="true"
  just-an-attribute
  interpolated="{{ $value }}"
/>
TEMAPLTE;

    $this->parameters = (new DocumentParser)
        ->onlyParseComponents()
        ->registerCustomComponentTags(['t'])
        ->parseTemplate($template)
        ->toDocument()
        ->getComponents()
        ->first()
        ->parameters;
});

test('it compiles attributes', function () {
    $expected = <<<'COMPILED'
['parameter'=>'content','binding'=>$theVariable,'short-hand'=>$shortHand,':escaped'=>'true','just-an-attribute'=>true,'interpolated'=>''.e($value).'']
COMPILED;

    expect($this->attributeCompiler->compile($this->parameters))->toBe($expected);
});

test('it can prefix escaped parameters', function () {
    $this->attributeCompiler->prefixEscapedParametersWith('attr:');
    $expected = <<<'COMPILED'
['parameter'=>'content','binding'=>$theVariable,'short-hand'=>$shortHand,'attr::escaped'=>'true','just-an-attribute'=>true,'interpolated'=>''.e($value).'']
COMPILED;

    expect($this->attributeCompiler->compile($this->parameters))->toBe($expected);
});

test('it can wrap param content in a callback', function () {
    $this->attributeCompiler->wrapResultIn(['binding', 'interpolated'], function ($value) {
        return "customFunctionToUse({$value})";
    });

    $expected = <<<'COMPILED'
['parameter'=>'content','binding'=>customFunctionToUse($theVariable),'short-hand'=>$shortHand,':escaped'=>'true','just-an-attribute'=>true,'interpolated'=>customFunctionToUse(''.e($value).'')]
COMPILED;

    expect($this->attributeCompiler->compile($this->parameters))->toBe($expected);
});
