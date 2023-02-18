<?php

namespace Stillat\BladeParser\Tests\Compiler;

use Stillat\BladeParser\Contracts\CustomComponentTagCompiler;
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Document\DocumentCompilerOptions;
use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Tests\ParserTestCase;

class CustomComponentTagCompilersTest extends ParserTestCase
{
    public function testCustomComponentTagCompilers()
    {
        $template = <<<'BLADE'
<a-custom />
BLADE;
        $doc = Document::fromText($template, customComponentTags: ['a']);

        $result = $doc->compile(new DocumentCompilerOptions(
            customTagCompilers: ['a' => new CustomCompiler]
        ));

        $this->assertSame('My custom compilation result!', $result);
    }

    public function testCoreComponentTagCompilersCanBeDisabled()
    {
        $template = <<<'BLADE'
<a-custom />
<x-profile />
BLADE;

        $doc = Document::fromText($template, customComponentTags: ['a']);

        $result = $doc->compile(new DocumentCompilerOptions(
            customTagCompilers: ['a' => new CustomCompiler],
            compileCoreComponentTags: false
        ));

        $expected = <<<'EXPECTED'
My custom compilation result!
<x-profile />
EXPECTED;

        $this->assertSame($expected, $result);
    }
}

class CustomCompiler implements CustomComponentTagCompiler
{
    public function compile(ComponentNode $component): ?string
    {
        return 'My custom compilation result!';
    }
}
