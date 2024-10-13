<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\EchoNode;
use Stillat\BladeParser\Nodes\EchoType;
use Stillat\BladeParser\Nodes\LiteralNode;
use Stillat\BladeParser\Nodes\PhpBlockNode;
use Stillat\BladeParser\Nodes\VerbatimNode;

test('literal documents', function () {
    $nodes = $this->parseNodes('Hello World');

    expect($nodes)->toHaveCount(1);
    $this->assertLiteralContent($nodes[0], 'Hello World');
});

test('core directives', function (string $directiveName) {
    $template = 'Start @'.$directiveName.' End';
    $nodes = $this->parseNodes($template);

    $this->assertLiteralContent($nodes[0], 'Start ');
    $this->assertLiteralContent($nodes[2], ' End');

    expect($nodes[1])->toBeInstanceOf(DirectiveNode::class);

    /** @var DirectiveNode $directive */
    $directive = $nodes[1];

    expect($directive->content)->toBe($directiveName);
    expect($directive->arguments)->toBeNull();
})->with(nonStructuralCoreDirectives());

test('core directives with multi byte characters', function (string $directiveName) {
    $template = '🐘 @'.$directiveName.' 🐘';
    $nodes = $this->parseNodes($template);

    $this->assertLiteralContent($nodes[0], '🐘 ');
    $this->assertLiteralContent($nodes[2], ' 🐘');

    expect($nodes[1])->toBeInstanceOf(DirectiveNode::class);

    /** @var DirectiveNode $directive */
    $directive = $nodes[1];

    expect($directive->content)->toBe($directiveName);
    expect($directive->arguments)->toBeNull();
})->with(nonStructuralCoreDirectives());

test('directives with arguments', function () {
    $template = <<<'EOT'
Start @can ('do something') End
EOT;

    $nodes = $this->parseNodes($template);

    expect($nodes)->toHaveCount(3);
    $this->assertLiteralContent($nodes[0], 'Start ');
    $this->assertLiteralContent($nodes[2], ' End');

    expect($nodes[1])->toBeInstanceOf(DirectiveNode::class);

    /** @var DirectiveNode $directive */
    $directive = $nodes[1];

    expect($directive->content)->toBe('can');
    expect($directive->arguments)->not->toBeNull();

    expect($directive->arguments->content)->toBe("('do something')");
    expect($directive->arguments->innerContent)->toBe("'do something'");
});

test('it parses neighboring nodes', function () {
    $template = '{{ $one }}{{ $two }}{{ $three }}';
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(3);

    $this->assertEchoContent($nodes[0], '{{ $one }}');
    $this->assertEchoContent($nodes[1], '{{ $two }}');
    $this->assertEchoContent($nodes[2], '{{ $three }}');
});

test('it parses components with multi byte characters', function () {
    $nodes = $this->parseNodes('<x-alert>🐘🐘🐘🐘</x-alert>');
    expect($nodes)->toHaveCount(3);
    expect($nodes[0])->toBeInstanceOf(ComponentNode::class);
    expect($nodes[1])->toBeInstanceOf(LiteralNode::class);
    $this->assertLiteralContent($nodes[1], '🐘🐘🐘🐘');
    expect($nodes[2])->toBeInstanceOf(ComponentNode::class);
    expect($nodes[2]->content)->toBe('</x-alert>');
    expect($nodes[0]->content)->toBe('<x-alert>');
});

test('it parses neighboring nodes with literals', function () {
    $template = 'a{{ $one }}b{{ $two }}c{{ $three }}d';
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(7);

    $this->assertLiteralContent($nodes[0], 'a');
    $this->assertEchoContent($nodes[1], '{{ $one }}');
    $this->assertLiteralContent($nodes[2], 'b');
    $this->assertEchoContent($nodes[3], '{{ $two }}');
    $this->assertLiteralContent($nodes[4], 'c');
    $this->assertEchoContent($nodes[5], '{{ $three }}');
    $this->assertLiteralContent($nodes[6], 'd');
});

test('it parses simple nodes', function () {
    $template = 'start {{ $variable }} end';
    $nodes = $this->parseNodes($template);

    expect($nodes)->toHaveCount(3);
    $this->assertLiteralContent($nodes[0], 'start ');
    $this->assertEchoContent($nodes[1], '{{ $variable }}');
    $this->assertLiteralContent($nodes[2], ' end');
});

test('it ignores escaped nodes', function () {
    $template = <<<'EOT'
@@unless
@{{ $variable }}
@{!! $variable }}
EOT;
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(1);

    $this->assertLiteralContent($nodes[0], $template);
});

test('it escapes nodes mixed with other nodes', function () {
    $template = <<<'EOT'
@@unless
@{{ $variable }}
@{!! $variable }}

{{ test }}


@{!! $variable }}

    {{ another }}
EOT;

    $literalOneContent = <<<'LITERAL'
@@unless
@{{ $variable }}
@{!! $variable }}


LITERAL;

    $literalTwoContent = <<<'LITERAL'



@{!! $variable }}

    
LITERAL;

    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(4);

    $this->assertLiteralContent($nodes[0], $literalOneContent);
    $this->assertEchoContent($nodes[1], '{{ test }}');
    $this->assertLiteralContent($nodes[2], $literalTwoContent);
    $this->assertEchoContent($nodes[3], '{{ another }}');
});

test('it parses many nodes', function () {
    $this->registerDirective(['props_two', 'props_three', 'directive']);

    $template = <<<'EOT'
start
    {{-- comment!!! --}}3
    s1@props_two(['color' => (true ?? 'gray')])
    s2@directive
    @directive something
    s3@props_three  (['color' => (true ?? 'gray')])
    @props(['color' => 'gray'])
 {!! $dooblyDoo !!}1
<ul {{ $attributes->merge(['class' => 'bg-'.$color.'-200']) }}>
    {{ $slot }}
</ul>
EOT;
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(19);

    $literalOneContent = <<<'LITERAL'
start
    
LITERAL;
    $this->assertLiteralContent($nodes[0], $literalOneContent);
    $this->assertCommentContent($nodes[1], '{{-- comment!!! --}}');

    $literalTwoContent = <<<'LITERAL'
3
    s1
LITERAL;
    $this->assertLiteralContent($nodes[2], $literalTwoContent);
    $this->assertDirectiveContent($nodes[3], 'props_two', "(['color' => (true ?? 'gray')])");

    $literalThreeContent = <<<'LITERAL'

    s2
LITERAL;
    $this->assertLiteralContent($nodes[4], $literalThreeContent);
    $this->assertDirectiveContent($nodes[5], 'directive');

    $literalFourContent = <<<'LITERAL'

    
LITERAL;
    $this->assertLiteralContent($nodes[6], $literalFourContent);
    $this->assertDirectiveName($nodes[7], 'directive');

    $literalFiveContent = <<<'LITERAL'
 something
    s3
LITERAL;
    $this->assertLiteralContent($nodes[8], $literalFiveContent);
    $this->assertDirectiveContent($nodes[9], 'props_three', "(['color' => (true ?? 'gray')])");

    $literalSixContent = <<<'LITERAL'

    
LITERAL;
    $this->assertLiteralContent($nodes[10], $literalSixContent);
    $this->assertDirectiveContent($nodes[11], 'props', "(['color' => 'gray'])");

    $literalSevenContent = <<<'LITERAL'

 
LITERAL;
    $this->assertLiteralContent($nodes[12], $literalSevenContent);

    expect($nodes[13])->toBeInstanceOf(EchoNode::class);
    expect($nodes[13]->type)->toBe(EchoType::RawEcho);
    expect($nodes[13]->content)->toBe('{!! $dooblyDoo !!}');

    $literalEightContent = <<<'LITERAL'
1
<ul 
LITERAL;
    $this->assertLiteralContent($nodes[14], $literalEightContent);

    $this->assertEchoContent($nodes[15], '{{ $attributes->merge([\'class\' => \'bg-\'.$color.\'-200\']) }}');

    $literalNineContent = <<<'LITERAL'
>
    
LITERAL;
    $this->assertLiteralContent($nodes[16], $literalNineContent);

    $this->assertEchoContent($nodes[17], '{{ $slot }}');

    $literalTenContent = <<<'LITERAL'

</ul>
LITERAL;
    $this->assertLiteralContent($nodes[18], $literalTenContent);
});

test('it parses simple template one', function () {
    $template = 'The current UNIX timestamp is {{ time() }}.';
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(3);

    $this->assertLiteralContent($nodes[0], 'The current UNIX timestamp is ');
    $this->assertEchoContent($nodes[1], '{{ time() }}');
    $this->assertLiteralContent($nodes[2], '.');
});

test('it parses simple template two', function () {
    $template = 'Hello, {!! $name !!}.';
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(3);

    $this->assertLiteralContent($nodes[0], 'Hello, ');
    $this->assertRawEchoNodeContent($nodes[1], '{!! $name !!}');
    $this->assertLiteralContent($nodes[2], '.');
});

test('it parses simple template three', function () {
    $template = <<<'EOT'
<h1>Laravel</h1>
 
Hello, @{{ name }}.
EOT;
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(1);

    $this->assertLiteralContent($nodes[0], $template);
});

test('it parses simple template four', function () {
    $template = '@@if';
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(1);
    $this->assertLiteralContent($nodes[0], $template);
});

test('it parses simple template five', function () {
    $template = <<<'EOT'
<script>
var app = {{ Illuminate\Support\Js::from($array) }};
</script>
EOT;
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(3);

    $literalContentOne = <<<'LITERAL'
<script>
var app = 
LITERAL;

    $this->assertLiteralContent($nodes[0], $literalContentOne);
    $this->assertEchoContent($nodes[1], '{{ Illuminate\Support\Js::from($array) }}');

    $literalContentTwo = <<<'LITERAL'
;
</script>
LITERAL;

    $this->assertLiteralContent($nodes[2], $literalContentTwo);
});

test('it parses simple template six', function () {
    $template = <<<'EOT'
<script>
var app = {{ Js::from($array) }};
</script>
EOT;
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(3);

    $literalContentOne = <<<'LITERAL'
<script>
var app = 
LITERAL;

    $this->assertLiteralContent($nodes[0], $literalContentOne);
    $this->assertEchoContent($nodes[1], '{{ Js::from($array) }}');

    $literalContentTwo = <<<'LITERAL'
;
</script>
LITERAL;
    $this->assertLiteralContent($nodes[2], $literalContentTwo);
});

test('it parses simple template seven', function () {
    $template = <<<'EOT'
@verbatim
<div class="container">
    Hello, {{ name }}.
</div>
@endverbatim
EOT;
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(1);
    expect($nodes[0])->toBeInstanceOf(VerbatimNode::class);

    /** @var VerbatimNode $verbatim */
    $verbatim = $nodes[0];

    expect($verbatim->content)->toBe($template);

    $innerContent = <<<'INNER'

<div class="container">
    Hello, {{ name }}.
</div>

INNER;

    expect($verbatim->innerContent)->toBe($innerContent);
});

test('it parses simple template eight', function () {
    $template = <<<'EOT'
@if (count($records) === 1)
I have one record!
@elseif (count($records) > 1)
I have multiple records!
@else
I don't have any records!
@endif
EOT;
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(7);

    $this->assertDirectiveContent($nodes[0], 'if', '(count($records) === 1)');

    $literalOne = <<<'LITERAL'

I have one record!

LITERAL;
    $this->assertLiteralContent($nodes[1], $literalOne);

    $this->assertDirectiveContent($nodes[2], 'elseif', '(count($records) > 1)');

    $literalTwo = <<<'LITERAL'

I have multiple records!

LITERAL;
    $this->assertLiteralContent($nodes[3], $literalTwo);

    $this->assertDirectiveContent($nodes[4], 'else');

    $literalThree = <<<'LITERAL'

I don't have any records!

LITERAL;
    $this->assertLiteralContent($nodes[5], $literalThree);

    $this->assertDirectiveContent($nodes[6], 'endif');
});

test('it parses simple template nine', function () {
    $template = <<<'EOT'
@unless (Auth::check())
You are not signed in.
@endunless
EOT;
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(3);

    $this->assertDirectiveContent($nodes[0], 'unless', '(Auth::check())');

    $literalOne = <<<'LITERAL'

You are not signed in.

LITERAL;
    $this->assertLiteralContent($nodes[1], $literalOne);
    $this->assertDirectiveContent($nodes[2], 'endunless');
});

test('it parses simple template ten', function () {
    $template = <<<'EOT'
@isset($records)
// $records is defined and is not null...
@endisset
EOT;
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(3);

    $this->assertDirectiveContent($nodes[0], 'isset', '($records)');

    $literalOne = <<<'LITERAL'

// $records is defined and is not null...

LITERAL;
    $this->assertLiteralContent($nodes[1], $literalOne);
    $this->assertDirectiveContent($nodes[2], 'endisset');
});

test('it parses simple template eleven', function () {
    $template = '{{ $name }}';
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(1);

    $this->assertEchoContent($nodes[0], $template);
});

test('it parses simple template twelve', function () {
    $template = '{{{ $name }}}';
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(1);
    $this->assertTripleEchoContent($nodes[0], $template);
});

test('it parses echo spanning multiple lines', function () {
    $template = <<<'EOT'
{{
         $name
 }}
EOT;
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(1);
    $this->assertEchoContent($nodes[0], $template);
});

test('blade inside php directive', function () {
    $template = <<<'EOT'
@php echo 'I am PHP {{ not Blade }}' @endphp
EOT;
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(1);
    expect($nodes[0])->toBeInstanceOf(PhpBlockNode::class);

    /** @var PhpBlockNode $phpNode */
    $phpNode = $nodes[0];
    expect($phpNode->content)->toBe($template);

    $innerContent = <<<'INNER'
 echo 'I am PHP {{ not Blade }}' 
INNER;
    expect($phpNode->innerContent)->toBe($innerContent);
});

test('it parses inline directives', function () {
    $template = '<div @if(true) yes @endif></div>';
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(5);

    $this->assertLiteralContent($nodes[0], '<div ');
    $this->assertDirectiveContent($nodes[1], 'if', '(true)');
    $this->assertLiteralContent($nodes[2], ' yes ');
    $this->assertDirectiveContent($nodes[3], 'endif');
    $this->assertLiteralContent($nodes[4], '></div>');
});

test('node whitespace on left', function () {
    $directive = $this->getDocument(' @lang')->findDirectiveByName('lang');
    expect($directive->hasWhitespaceOnLeft())->toBeTrue();
    expect($directive->hasWhitespaceOnRight())->toBeFalse();
});

test('node whitespace on right', function () {
    $directive = $this->getDocument('@lang ')->findDirectiveByName('lang');
    expect($directive->hasWhitespaceOnLeft())->toBeFalse();
    expect($directive->hasWhitespaceOnRight())->toBeTrue();
});

test('node whitespace both', function () {
    $directive = $this->getDocument(' @lang ')->findDirectiveByName('lang');
    expect($directive->hasWhitespaceOnLeft())->toBeTrue();
    expect($directive->hasWhitespaceOnRight())->toBeTrue();
});

test('node whitespace none', function () {
    $directive = $this->getDocument('@lang')->findDirectiveByName('lang');
    expect($directive->hasWhitespaceOnLeft())->toBeFalse();
    expect($directive->hasWhitespaceOnRight())->toBeFalse();
});

test('node document association', function () {
    $doc = $this->getDocument(' @lang ');
    $directive = $doc->findDirectiveByName('lang');

    expect($directive->hasDocument())->toBeTrue();
    expect($directive->getDocument())->toBe($doc);
});

test('get node returns same instance', function () {
    $doc = $this->getDocument(' @lang ');
    $directive = $doc->findDirectiveByName('lang');

    expect($directive->getNode())->toBe($directive);
});

test('start indent level', function () {
    $directive = $this->getDocument('     @lang ')->findDirectiveByName('lang');
    expect($directive->getStartIndentationLevel())->toBe(5);
});

test('not directives without leading spaces are handled correctly', function () {
    $template = <<<'EOT'
<input type="text" id="emailBackdrop" class="form-control" placeholder="xxxx@xxx.xx">
EOT;
    $doc = $this->getDocument($template);
    expect($doc->getDirectives())->toHaveCount(0);
    expect((string) $doc)->toBe($template);

    $this->registerDirective('xxx');
    $doc = $this->getDocument($template);
    expect($doc->getDirectives())->toHaveCount(1);
    expect((string) $doc)->toBe($template);
});
