<?php

namespace Stillat\BladeParser\Tests\Fragments;

use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\EchoNode;
use Stillat\BladeParser\Nodes\Fragments\FragmentParameterType;
use Stillat\BladeParser\Nodes\Fragments\FragmentPosition;
use Stillat\BladeParser\Nodes\Fragments\HtmlFragment;
use Stillat\BladeParser\Tests\ParserTestCase;

class BasicFragmentsTest extends ParserTestCase
{
    public function testBasicFragments()
    {
        $template = <<<'EOT'
<div></div>
EOT;
        $fragments = Document::fromText($template)->getFragments();

        $this->assertCount(2, $fragments);

        /** @var HtmlFragment $fragmentOne */
        $fragmentOne = $fragments[0];
        $this->assertSame('div', $fragmentOne->tagName);
        $this->assertSame('div', $fragmentOne->documentContent);
        $this->assertSame('<div>', $fragmentOne->content);
        $this->assertFalse($fragmentOne->isSelfClosing);
        $this->assertFalse($fragmentOne->isClosingTag);

        /** @var HtmlFragment $fragmentTwo */
        $fragmentTwo = $fragments[1];
        $this->assertSame('div', $fragmentTwo->tagName);
        $this->assertSame('div', $fragmentTwo->documentContent);
        $this->assertSame('</div>', $fragmentTwo->content);
        $this->assertFalse($fragmentTwo->isSelfClosing);
        $this->assertTrue($fragmentTwo->isClosingTag);
    }

    public function testSelfClosingFragments()
    {
        $template = <<<'EOT'
<span v-text="title" />
EOT;
        $fragments = Document::fromText($template)->getFragments();

        $this->assertCount(1, $fragments);

        /** @var HtmlFragment $fragment */
        $fragment = $fragments[0];

        $this->assertFalse($fragment->isClosingTag);
        $this->assertTrue($fragment->isSelfClosing);

        $this->assertSame('<span v-text="title" />', $fragment->content);
        $this->assertSame('span v-text="title" ', $fragment->documentContent);
    }

    public function testFragmentPositions()
    {
        $template = <<<'EOT'
<div>
    <span text="hello, world">Content</span>
</div>
EOT;
        /** @var HtmlFragment[] $fragments */
        $fragments = Document::fromText($template)->getFragments();
        $this->assertCount(4, $fragments);

        $f1 = $fragments[0];
        $this->assertFragmentPosition($f1, 1, 1, 1, 5);
        $this->assertFragmentNamePosition($f1, 1, 2, 1, 4);

        $f2 = $fragments[1];
        $this->assertFragmentPosition($f2, 2, 5, 2, 30);
        $this->assertFragmentNamePosition($f2, 2, 6, 2, 9);

        $this->assertCount(1, $f2->parameters);
        $p1 = $f2->parameters[0];
        $this->assertSame('text="hello, world"', $p1->content);
        $this->assertSame('text', $p1->name);
        $this->assertSame('"hello, world"', $p1->value);
        $this->assertSame(FragmentParameterType::Parameter, $p1->type);
        $this->assertFragmentParameterPosition($p1, 2, 11, 2, 29);

        $f3 = $fragments[2];
        $this->assertFragmentPosition($f3, 2, 38, 2, 44);
        $this->assertFragmentNamePosition($f3, 2, 40, 2, 43);

        $f4 = $fragments[3];
        $this->assertFragmentPosition($f4, 3, 1, 3, 6);
        $this->assertFragmentNamePosition($f4, 3, 3, 3, 5);
    }

    public function testFragmentParameters()
    {
        $template = <<<'EOT'
<input type="text" required />
EOT;
        /** @var HtmlFragment[] $fragments */
        $fragments = Document::fromText($template)->getFragments();

        $this->assertCount(1, $fragments);

        $f1 = $fragments[0];

        $this->assertCount(2, $f1->parameters);

        $p1 = $f1->parameters[0];
        $this->assertSame('type="text"', $p1->content);
        $this->assertSame('type', $p1->name);
        $this->assertSame('"text"', $p1->value);
        $this->assertSame('text', $p1->getValue());
        $this->assertSame('text', $f1->getParameter('type')->getValue());
        $this->assertSame(FragmentParameterType::Parameter, $p1->type);

        $p2 = $f1->parameters[1];
        $this->assertSame('required', $p2->content);
        $this->assertSame('required', $p2->name);
        $this->assertSame('', $p2->value);
        $this->assertSame('', $f1->getParameter('required')->getValue());
        $this->assertSame(FragmentParameterType::Attribute, $p2->type);
    }

    public function testNodeFragmentPositions()
    {
        $template = <<<'EOT'
<span class=" @if ($something) mb-10 @endif "> {{ $var }} </span>

<{{ $element }}>
    <p {{ $attributes }}>
    
    </p>
</{{ $element }}>

<span this{{ $attribute }}>

</span>
EOT;
        $doc = Document::fromText($template);
        $doc->getFragments();

        /** @var DirectiveNode[] $directives */
        $directives = $doc->getDirectives();
        $if = $directives[0];
        $this->assertSame(FragmentPosition::InsideParameter, $if->fragmentPosition);

        $endIf = $directives[1];
        $this->assertSame(FragmentPosition::InsideParameter, $endIf->fragmentPosition);

        /** @var EchoNode[] $echoes */
        $echoes = $doc->getEchoes();
        $varEcho = $echoes[0];
        $this->assertSame(FragmentPosition::Unknown, $varEcho->fragmentPosition);

        $elementEcho = $echoes[1];
        $this->assertSame(FragmentPosition::InsideFragmentName, $elementEcho->fragmentPosition);

        $attributesEcho = $echoes[2];
        $this->assertSame(FragmentPosition::InsideFragment, $attributesEcho->fragmentPosition);

        $closeElement = $echoes[3];
        $this->assertSame(FragmentPosition::InsideFragmentName, $closeElement->fragmentPosition);
    }

    public function testFragmentHelperMethods()
    {
        $template = <<<'EOT'
<span class=" @if ($something) mb-10 @endif "> {{ $var }} </span>

<{{ $element }}>
    <p {{ $attributes }}>
    
    </p>
</{{ $element }}>

<span this{{ $attribute }}>

</span>

<span this{{ $attribute }} {{ $title }}>

</span>
EOT;
        $doc = Document::fromText($template)->resolveFragments();

        $this->assertCount(2, $doc->getNodes()->where(fn (AbstractNode $n) => $n->isInHtmlParameter()));
        $this->assertCount(2, $doc->getNodes()->where(fn (AbstractNode $n) => $n->isInHtmlTagName()));
        $this->assertCount(4, $doc->getNodes()->where(fn (AbstractNode $n) => $n->isInHtmlTagContent()));
        $this->assertCount(1, $doc->getNodes()->where(fn (AbstractNode $n) => $n->isBetweenHtmlFragments()));
    }
}
