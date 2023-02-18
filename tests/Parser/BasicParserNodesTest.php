<?php

namespace Stillat\BladeParser\Tests\Parser;

use Stillat\BladeParser\Compiler\CompilerServices\CoreDirectiveRetriever;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\EchoNode;
use Stillat\BladeParser\Nodes\EchoType;
use Stillat\BladeParser\Nodes\PhpBlockNode;
use Stillat\BladeParser\Nodes\VerbatimNode;
use Stillat\BladeParser\Tests\ParserTestCase;

class BasicParserNodesTest extends ParserTestCase
{
    public function testLiteralDocuments()
    {
        $nodes = $this->parseNodes('Hello World');

        $this->assertCount(1, $nodes);
        $this->assertLiteralContent($nodes[0], 'Hello World');
    }

    /**
     * @dataProvider coreDirectives
     */
    public function testCoreDirectives(string $directiveName)
    {
        $template = 'Start @'.$directiveName.' End';
        $nodes = $this->parseNodes($template);

        $this->assertLiteralContent($nodes[0], 'Start ');
        $this->assertLiteralContent($nodes[2], ' End');

        $this->assertInstanceOf(DirectiveNode::class, $nodes[1]);

        /** @var DirectiveNode $directive */
        $directive = $nodes[1];

        $this->assertSame($directiveName, $directive->content);
        $this->assertNull($directive->arguments);
    }

    public function testDirectivesWithArguments()
    {
        $template = <<<'EOT'
Start @can ('do something') End
EOT;

        $nodes = $this->parseNodes($template);

        $this->assertCount(3, $nodes);
        $this->assertLiteralContent($nodes[0], 'Start ');
        $this->assertLiteralContent($nodes[2], ' End');

        $this->assertInstanceOf(DirectiveNode::class, $nodes[1]);

        /** @var DirectiveNode $directive */
        $directive = $nodes[1];

        $this->assertSame('can', $directive->content);
        $this->assertNotNull($directive->arguments);

        $this->assertSame("('do something')", $directive->arguments->content);
        $this->assertSame("'do something'", $directive->arguments->innerContent);
    }

    public function testItParsesNeighboringNodes()
    {
        $template = '{{ $one }}{{ $two }}{{ $three }}';
        $nodes = $this->parseNodes($template);
        $this->assertCount(3, $nodes);

        $this->assertEchoContent($nodes[0], '{{ $one }}');
        $this->assertEchoContent($nodes[1], '{{ $two }}');
        $this->assertEchoContent($nodes[2], '{{ $three }}');
    }

    public function testItParsesNeighboringNodesWithLiterals()
    {
        $template = 'a{{ $one }}b{{ $two }}c{{ $three }}d';
        $nodes = $this->parseNodes($template);
        $this->assertCount(7, $nodes);

        $this->assertLiteralContent($nodes[0], 'a');
        $this->assertEchoContent($nodes[1], '{{ $one }}');
        $this->assertLiteralContent($nodes[2], 'b');
        $this->assertEchoContent($nodes[3], '{{ $two }}');
        $this->assertLiteralContent($nodes[4], 'c');
        $this->assertEchoContent($nodes[5], '{{ $three }}');
        $this->assertLiteralContent($nodes[6], 'd');
    }

    public function testItParsesSimpleNodes()
    {
        $template = 'start {{ $variable }} end';
        $nodes = $this->parseNodes($template);

        $this->assertCount(3, $nodes);
        $this->assertLiteralContent($nodes[0], 'start ');
        $this->assertEchoContent($nodes[1], '{{ $variable }}');
        $this->assertLiteralContent($nodes[2], ' end');
    }

    public function testItIgnoresEscapedNodes()
    {
        $template = <<<'EOT'
@@unless
@{{ $variable }}
@{!! $variable }}
EOT;
        $nodes = $this->parseNodes($template);
        $this->assertCount(1, $nodes);

        $this->assertLiteralContent($nodes[0], $template);
    }

    public function testItEscapesNodesMixedWithOtherNodes()
    {
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
        $this->assertCount(4, $nodes);

        $this->assertLiteralContent($nodes[0], $literalOneContent);
        $this->assertEchoContent($nodes[1], '{{ test }}');
        $this->assertLiteralContent($nodes[2], $literalTwoContent);
        $this->assertEchoContent($nodes[3], '{{ another }}');
    }

    public function testItParsesManyNodes()
    {
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
        $this->assertCount(19, $nodes);

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

        $this->assertInstanceOf(EchoNode::class, $nodes[13]);
        $this->assertSame(EchoType::RawEcho, $nodes[13]->type);
        $this->assertSame('{!! $dooblyDoo !!}', $nodes[13]->content);

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
    }

    public function testItParsesSimpleTemplateOne()
    {
        $template = 'The current UNIX timestamp is {{ time() }}.';
        $nodes = $this->parseNodes($template);
        $this->assertCount(3, $nodes);

        $this->assertLiteralContent($nodes[0], 'The current UNIX timestamp is ');
        $this->assertEchoContent($nodes[1], '{{ time() }}');
        $this->assertLiteralContent($nodes[2], '.');
    }

    public function testItParsesSimpleTemplateTwo()
    {
        $template = 'Hello, {!! $name !!}.';
        $nodes = $this->parseNodes($template);
        $this->assertCount(3, $nodes);

        $this->assertLiteralContent($nodes[0], 'Hello, ');
        $this->assertRawEchoNodeContent($nodes[1], '{!! $name !!}');
        $this->assertLiteralContent($nodes[2], '.');
    }

    public function testItParsesSimpleTemplateThree()
    {
        $template = <<<'EOT'
<h1>Laravel</h1>
 
Hello, @{{ name }}.
EOT;
        $nodes = $this->parseNodes($template);
        $this->assertCount(1, $nodes);

        $this->assertLiteralContent($nodes[0], $template);
    }

    public function testItParsesSimpleTemplateFour()
    {
        $template = '@@if';
        $nodes = $this->parseNodes($template);
        $this->assertCount(1, $nodes);
        $this->assertLiteralContent($nodes[0], $template);
    }

    public function testItParsesSimpleTemplateFive()
    {
        $template = <<<'EOT'
<script>
var app = {{ Illuminate\Support\Js::from($array) }};
</script>
EOT;
        $nodes = $this->parseNodes($template);
        $this->assertCount(3, $nodes);

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
    }

    public function testItParsesSimpleTemplateSix()
    {
        $template = <<<'EOT'
<script>
var app = {{ Js::from($array) }};
</script>
EOT;
        $nodes = $this->parseNodes($template);
        $this->assertCount(3, $nodes);

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
    }

    public function testItParsesSimpleTemplateSeven()
    {
        $template = <<<'EOT'
@verbatim
<div class="container">
    Hello, {{ name }}.
</div>
@endverbatim
EOT;
        $nodes = $this->parseNodes($template);
        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(VerbatimNode::class, $nodes[0]);

        /** @var VerbatimNode $verbatim */
        $verbatim = $nodes[0];

        $this->assertSame($template, $verbatim->content);

        $innerContent = <<<'INNER'

<div class="container">
    Hello, {{ name }}.
</div>

INNER;

        $this->assertSame($innerContent, $verbatim->innerContent);
    }

    public function testItParsesSimpleTemplateEight()
    {
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
        $this->assertCount(7, $nodes);

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
    }

    public function testItParsesSimpleTemplateNine()
    {
        $template = <<<'EOT'
@unless (Auth::check())
You are not signed in.
@endunless
EOT;
        $nodes = $this->parseNodes($template);
        $this->assertCount(3, $nodes);

        $this->assertDirectiveContent($nodes[0], 'unless', '(Auth::check())');

        $literalOne = <<<'LITERAL'

You are not signed in.

LITERAL;
        $this->assertLiteralContent($nodes[1], $literalOne);
        $this->assertDirectiveContent($nodes[2], 'endunless');
    }

    public function testItParsesSimpleTemplateTen()
    {
        $template = <<<'EOT'
@isset($records)
// $records is defined and is not null...
@endisset
EOT;
        $nodes = $this->parseNodes($template);
        $this->assertCount(3, $nodes);

        $this->assertDirectiveContent($nodes[0], 'isset', '($records)');

        $literalOne = <<<'LITERAL'

// $records is defined and is not null...

LITERAL;
        $this->assertLiteralContent($nodes[1], $literalOne);
        $this->assertDirectiveContent($nodes[2], 'endisset');
    }

    public function testItParsesSimpleTemplateEleven()
    {
        $template = '{{ $name }}';
        $nodes = $this->parseNodes($template);
        $this->assertCount(1, $nodes);

        $this->assertEchoContent($nodes[0], $template);
    }

    public function testItParsesSimpleTemplateTwelve()
    {
        $template = '{{{ $name }}}';
        $nodes = $this->parseNodes($template);
        $this->assertCount(1, $nodes);
        $this->assertTripleEchoContent($nodes[0], $template);
    }

    public function testItParsesEchoSpanningMultipleLines()
    {
        $template = <<<'EOT'
{{
         $name
 }}
EOT;
        $nodes = $this->parseNodes($template);
        $this->assertCount(1, $nodes);
        $this->assertEchoContent($nodes[0], $template);
    }

    public function testBladeInsidePhpDirective()
    {
        $template = <<<'EOT'
@php echo 'I am PHP {{ not Blade }}' @endphp
EOT;
        $nodes = $this->parseNodes($template);
        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(PhpBlockNode::class, $nodes[0]);

        /** @var PhpBlockNode $phpNode */
        $phpNode = $nodes[0];
        $this->assertSame($template, $phpNode->content);

        $innerContent = <<<'INNER'
 echo 'I am PHP {{ not Blade }}' 
INNER;
        $this->assertSame($innerContent, $phpNode->innerContent);
    }

    public function testItParsesInlineDirectives()
    {
        $template = '<div @if(true) yes @endif></div>';
        $nodes = $this->parseNodes($template);
        $this->assertCount(5, $nodes);

        $this->assertLiteralContent($nodes[0], '<div ');
        $this->assertDirectiveContent($nodes[1], 'if', '(true)');
        $this->assertLiteralContent($nodes[2], ' yes ');
        $this->assertDirectiveContent($nodes[3], 'endif');
        $this->assertLiteralContent($nodes[4], '></div>');
    }

    public function testNodeWhitespaceOnLeft()
    {
        $directive = $this->getDocument(' @lang')->findDirectiveByName('lang');
        $this->assertTrue($directive->hasWhitespaceOnLeft());
        $this->assertFalse($directive->hasWhitespaceOnRight());
    }

    public function testNodeWhitespaceOnRight()
    {
        $directive = $this->getDocument('@lang ')->findDirectiveByName('lang');
        $this->assertFalse($directive->hasWhitespaceOnLeft());
        $this->assertTrue($directive->hasWhitespaceOnRight());
    }

    public function testNodeWhitespaceBoth()
    {
        $directive = $this->getDocument(' @lang ')->findDirectiveByName('lang');
        $this->assertTrue($directive->hasWhitespaceOnLeft());
        $this->assertTrue($directive->hasWhitespaceOnRight());
    }

    public function testNodeWhitespaceNone()
    {
        $directive = $this->getDocument('@lang')->findDirectiveByName('lang');
        $this->assertFalse($directive->hasWhitespaceOnLeft());
        $this->assertFalse($directive->hasWhitespaceOnRight());
    }

    public function testNodeDocumentAssociation()
    {
        $doc = $this->getDocument(' @lang ');
        $directive = $doc->findDirectiveByName('lang');

        $this->assertTrue($directive->hasDocument());
        $this->assertSame($doc, $directive->getDocument());
    }

    public function testGetNodeReturnsSameInstance()
    {
        $doc = $this->getDocument(' @lang ');
        $directive = $doc->findDirectiveByName('lang');

        $this->assertSame($directive, $directive->getNode());
    }

    public function testStartIndentLevel()
    {
        $directive = $this->getDocument('     @lang ')->findDirectiveByName('lang');
        $this->assertSame(5, $directive->getStartIndentationLevel());
    }

    public function testNotDirectivesWithoutLeadingSpacesAreHandledCorrectly()
    {
        $template = <<<'EOT'
<input type="text" id="emailBackdrop" class="form-control" placeholder="xxxx@xxx.xx">
EOT;
        $doc = $this->getDocument($template);
        $this->assertCount(0, $doc->getDirectives());
        $this->assertSame($template, (string) $doc);

        $this->registerDirective('xxx');
        $doc = $this->getDocument($template);
        $this->assertCount(1, $doc->getDirectives());
        $this->assertSame($template, (string) $doc);
    }

    public function coreDirectives()
    {
        return collect(CoreDirectiveRetriever::instance()->getNonStructureDirectiveNames())->map(function ($name) {
            return [$name];
        })->all();
    }
}
