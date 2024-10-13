<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Document\DocumentCompilerOptions;

test('custom component tag compilers', function () {
    $template = <<<'BLADE'
<a-custom />
BLADE;
    $doc = Document::fromText($template, customComponentTags: ['a']);

    $result = $doc->compile(new DocumentCompilerOptions(
        customTagCompilers: ['a' => new \Stillat\BladeParser\Tests\Compiler\CustomCompiler]
    ));

    expect($result)->toBe('My custom compilation result!');
});

test('core component tag compilers can be disabled', function () {
    $template = <<<'BLADE'
<a-custom />
<x-profile />
BLADE;

    $doc = Document::fromText($template, customComponentTags: ['a']);

    $result = $doc->compile(new DocumentCompilerOptions(
        customTagCompilers: ['a' => new \Stillat\BladeParser\Tests\Compiler\CustomCompiler],
        compileCoreComponentTags: false
    ));

    $expected = <<<'EXPECTED'
My custom compilation result!
<x-profile />
EXPECTED;

    expect($result)->toBe($expected);
});
