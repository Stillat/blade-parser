<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Nodes\VerbatimNode;

test('verbatim does not create additional nodes', function () {
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
    expect($nodes)->toHaveCount(3);
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
    expect($nodes[1])->toBeInstanceOf(VerbatimNode::class);

    /** @var VerbatimNode $verbatim */
    $verbatim = $nodes[1];

    expect($verbatim->content)->toBe($outerContent);
    expect($verbatim->innerContent)->toBe($innerContent);
});
