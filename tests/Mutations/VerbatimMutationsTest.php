<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('verbatim content can be changed', function () {
    $template = <<<'EOT'
One
@verbatim
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
@endverbatim
Two
EOT;
    $doc = $this->getDocument($template);
    $doc->getVerbatimBlocks()->first()->setContent('{{ some_new_content }}');

    $expected = <<<'EXPECTED'
One
@verbatim 
{{ some_new_content }}
@endverbatim
Two
EXPECTED;

    expect((string) $doc)->toBe($expected);
});

test('verbatim original whitespace can be overridden', function () {
    $template = <<<'EOT'
One
@verbatim                 
                      {{ something }}
                      
                   @endverbatim
Two
EOT;
    $doc = $this->getDocument($template);
    $doc->getVerbatimBlocks()->first()->setContent('{{ something_else }}', false);

    $expected = <<<'EXPECTED'
One
@verbatim {{ something_else }} @endverbatim
Two
EXPECTED;

    expect((string) $doc)->toBe($expected);
});
