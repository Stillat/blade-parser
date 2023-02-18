<?php

namespace Stillat\BladeParser\Tests\Parser;

use Stillat\BladeParser\Compiler\CompilerServices\CoreDirectiveRetriever;
use Stillat\BladeParser\Nodes\LiteralNode;
use Stillat\BladeParser\Tests\ParserTestCase;

class EscapedContentTest extends ParserTestCase
{
    public function testBasicEscapedContent()
    {
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
        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(LiteralNode::class, $nodes[0]);

        /** @var LiteralNode $literal */
        $literal = $nodes[0];

        $this->assertSame($template, $literal->content);
        $this->assertSame($expected, $literal->unescapedContent);
    }

    public function testNestedEscapedDirectives()
    {
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

        $this->assertSame($expected, $this->getDocument($template)->compile());
    }

    /**
     * @dataProvider coreDirectiveEscapedContent
     *
     * @return void
     */
    public function testCoreDirectivesEscapedOutput($template, $output)
    {
        $this->assertSame($output, $this->compiler->compileString($template));
    }

    public function coreDirectiveEscapedContent()
    {
        return collect(CoreDirectiveRetriever::instance()->getDirectiveNames())->map(function ($name) {
            return ['@@'.$name, '@'.$name];
        })->all();
    }
}
