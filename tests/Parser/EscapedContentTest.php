<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Compiler\CompilerServices\CoreDirectiveRetriever;
use Stillat\BladeParser\Nodes\LiteralNode;

test('basic escaped content', function () {
    $template = <<<'EOT'
@{{ $variable }}
@{!! $variable !!}
@@directive
@{{ $var
@{{{ $variable }}}
EOT;
    $expected = <<<'EXPECTED'
{{ $variable }}
{!! $variable !!}
@directive
{{ $var
{{{ $variable }}}
EXPECTED;
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(1);
    expect($nodes[0])->toBeInstanceOf(LiteralNode::class);

    /** @var LiteralNode $literal */
    $literal = $nodes[0];

    expect($literal->content)->toBe($template);
    expect($literal->unescapedContent)->toBe($expected);
});

test('nested escaped directives', function () {
    $template = <<<'EOT'
@php
    $arrayOne = [];
    $arrayTwo = [];
@endphp

@foreach($arrayOne as $val)
    @if($val == 'something')
        <div></div>
    @elseif($val == 'somethingElse')
        @@foreach($arrayTwo as $aDifferentValue)
            <div></div>
        @@endforeach
    @else
        <div></div>
    @endif
@endforeach
EOT;

    $expected = <<<'EOT'
<?php $arrayOne = [];
    $arrayTwo = []; ?>

<?php $__currentLoopData = $arrayOne; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if($val == 'something'): ?>
        <div></div>
    <?php elseif($val == 'somethingElse'): ?>
        @foreach($arrayTwo as $aDifferentValue)
            <div></div>
        @endforeach
    <?php else: ?>
        <div></div>
    <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
EOT;

    expect($this->getDocument($template)->compile())->toBe($expected);
});

test('core directives escaped output', function ($template, $output) {
    expect($this->compiler->compileString($template))->toBe($output);
})->with(coreDirectiveEscapedContent());

function coreDirectiveEscapedContent(): array
{
    return collect(CoreDirectiveRetriever::instance()->getDirectiveNames())->map(function ($name) {
        return ['@@'.$name, '@'.$name];
    })->all();
}
