<?php

namespace Stillat\BladeParser\Tests\Compiler;

use Stillat\BladeParser\Compiler\Transformers\NodeTransformer;
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Document\DocumentOptions;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Tests\ParserTestCase;

class NodeTransformerTest extends ParserTestCase
{
    private function transform(string $template, bool $withCoreDirectives): string
    {
        $doc = Document::fromText($template, documentOptions: new DocumentOptions(
            withCoreDirectives: $withCoreDirectives,
            customDirectives: ['custom', 'endcustom']
        ))->resolveStructures();

        return (new CustomTransformer())->transformDocument($doc);
    }

    public function testNodeTransformerCanTransformSimpleNodes()
    {
        $blade = <<<'BLADE'
The beginning.

@custom
    Hello, world!
@endcustom

The end.
BLADE;

        $result = $this->transform($blade, true);

        $expected = <<<'EXPECTED'
The beginning.

@include("something-here")

The end.
EXPECTED;

        $this->assertSame($expected, $result);
    }

    /**
     * @dataProvider templateProvider
     */
    public function testNodeTransformerCanTransformNodes($template)
    {
        $insert = <<<'BLADE'

@custom
    Hello, world!
@endcustom

BLADE;

        $insertExpected = <<<'BLADE'

@include("something-here")

BLADE;

        $blade = $template.$insert.$template;
        $expected = $template.$insertExpected.$template;

        $this->assertSame($expected, $this->transform($blade, true));
    }

    /**
     * @dataProvider templateProvider
     */
    public function testNodeTransformerCanTransformNodesWithoutCoreDirectives($template)
    {
        $insert = <<<'BLADE'

@custom
    Hello, world!
@endcustom

BLADE;

        $insertExpected = <<<'BLADE'

@include("something-here")

BLADE;

        $blade = $template.$insert.$template;
        $expected = $template.$insertExpected.$template;

        $this->assertSame($expected, $this->transform($blade, false));
    }

    public static function templateProvider(): array
    {
        $templates = [
            '@if (true) something @endif',
            '@if (true) @if (false) Hello! @endif @endif',
            'Some text
@verbatim
    {{ $a }}
    @if($b)
        {{ $b }}
    @endif
@endverbatim',
            '@for ($i = 0; $i < 10; $i++)
test
@break
@endfor',
            '@for ($i = 0; $i < 10; $i++)
test
@break(-2)
@endfor',
            <<<'BLADE'
@cannot ('update', [$post])
breeze
@elsecannot('delete', [$post])
sneeze
@endcannot
BLADE,
            '{{-- this is a comment --}}',
            '@componentFirst(["one", "two"])',
            '<x-slot name="foo"></x-slot>',
            '@for ($i = 0; $i < 10; $i++)
test
@continue(TRUE)
@endfor',
            '{{ $name }}',
            '@{{ $name }}',
            '@verbatim {{ $a }} @endverbatim {{ $b }} @verbatim {{ $c }} @endverbatim',
            '@php echo "#1"; @endphp @verbatim {{ #2 }} @endverbatim @verbatim {{ #3 }} @endverbatim @php echo "#4"; @endphp',
            '{{ $first }}
@php
    echo $second;
@endphp
@if ($conditional)
    {{ $third }}
@endif
@include("users")
@verbatim
    {{ $fourth }} @include("test")
@endverbatim
@php echo $fifth; @endphp',
            '{{-- @verbatim Block #1 @endverbatim --}} @php "Block #2" @endphp',
            '@forelse ($this->getUsers() as $user)
breeze
@empty
empty
@endforelse',
            '<?php echo "Hello, world!"; ?>',
            '@php ($var = "Hello, world!")',
        ];
        $templatesToTest = [];
        $bigTemplate = '';

        foreach ($templates as $template) {
            $template = "\n".$template."\n";
            $templatesToTest[] = [$template];
            $bigTemplate .= $template;

            if ($template != $bigTemplate) {
                $templatesToTest[] = [$bigTemplate];
            }
        }

        return $templatesToTest;
    }
}

class CustomTransformer extends NodeTransformer
{
    public function transformNode($node): ?string
    {
        if (! $node instanceof DirectiveNode || $node->content != 'custom') {
            return null;
        }

        $this->skipToNode($node->isClosedBy);

        return '@include("something-here")';
    }
}
