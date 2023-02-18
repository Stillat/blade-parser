<?php

namespace Stillat\BladeParser\Tests\Mutations;

use Stillat\BladeParser\Tests\ParserTestCase;

class VerbatimMutationsTest extends ParserTestCase
{
    public function testVerbatimContentCanBeChanged()
    {
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

        $this->assertSame($expected, (string) $doc);
    }

    public function testVerbatimOriginalWhitespaceCanBeOverridden()
    {
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

        $this->assertSame($expected, (string) $doc);
    }
}
