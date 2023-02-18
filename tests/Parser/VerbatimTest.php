<?php

namespace Stillat\BladeParser\Tests\Parser;

use Stillat\BladeParser\Nodes\VerbatimNode;
use Stillat\BladeParser\Tests\ParserTestCase;

class VerbatimTest extends ParserTestCase
{
    public function testVerbatimDoesNotCreateAdditionalNodes()
    {
        $template = <<<'EOT'
start @verbatim start

start
{{-- comment!!! --}}3
s1@props-two(['color' => (true ?? 'gray')])
s2@directive
@directive something
s3@props-three  (['color' => (true ?? 'gray')])
@props(['color' => 'gray'])
{!! $dooblyDoo !!}1
<ul {{ $attributes->merge(['class' => 'bg-'.$color.'-200']) }}>
{{ $slot }}
</ul>

end @endverbatim end
EOT;
        $nodes = $this->parseNodes($template);
        $this->assertCount(3, $nodes);
        $this->assertLiteralContent($nodes[0], 'start ');
        $this->assertLiteralContent($nodes[2], ' end');

        $outerContent = <<<'CONTENT'
@verbatim start

start
{{-- comment!!! --}}3
s1@props-two(['color' => (true ?? 'gray')])
s2@directive
@directive something
s3@props-three  (['color' => (true ?? 'gray')])
@props(['color' => 'gray'])
{!! $dooblyDoo !!}1
<ul {{ $attributes->merge(['class' => 'bg-'.$color.'-200']) }}>
{{ $slot }}
</ul>

end @endverbatim
CONTENT;

        $innerContent = <<<'INNER'
 start

start
{{-- comment!!! --}}3
s1@props-two(['color' => (true ?? 'gray')])
s2@directive
@directive something
s3@props-three  (['color' => (true ?? 'gray')])
@props(['color' => 'gray'])
{!! $dooblyDoo !!}1
<ul {{ $attributes->merge(['class' => 'bg-'.$color.'-200']) }}>
{{ $slot }}
</ul>

end 
INNER;
        $this->assertInstanceOf(VerbatimNode::class, $nodes[1]);

        /** @var VerbatimNode $verbatim */
        $verbatim = $nodes[1];

        $this->assertSame($outerContent, $verbatim->content);
        $this->assertSame($innerContent, $verbatim->innerContent);
    }
}
