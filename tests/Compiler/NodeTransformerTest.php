<?php

use Stillat\BladeParser\Document\Document;
use \Stillat\BladeParser\Compiler\Transformers\NodeTransformer;
use \Stillat\BladeParser\Tests\Compiler\CustomTransformer;
use Stillat\BladeParser\Document\DocumentOptions;
use Stillat\BladeParser\Nodes\DirectiveNode;

test('node transformer can transform simple nodes', function () {
    $blade = <<<'BLADE'
The beginning.

@custom
    Hello, world!
@endcustom

The end.
BLADE;

    $result = transformDocument($blade, true);

    $expected = <<<'EXPECTED'
The beginning.

@include("something-here")

The end.
EXPECTED;

    expect($result)->toBe($expected);
});

test('node transformer can transform nodes', function ($template) {
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

    expect(transformDocument($blade, true))->toBe($expected);
})->with(templateProvider());

test('node transformer can transform nodes without core directives', function ($template) {
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

    expect(transformDocument($blade, false))->toBe($expected);
})->with(templateProvider());

function templateProvider(): array
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

