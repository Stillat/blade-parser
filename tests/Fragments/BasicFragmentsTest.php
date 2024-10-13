<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\EchoNode;
use Stillat\BladeParser\Nodes\Fragments\FragmentParameterType;
use Stillat\BladeParser\Nodes\Fragments\FragmentPosition;
use Stillat\BladeParser\Nodes\Fragments\HtmlFragment;

test('basic fragments', function () {
    $template = <<<'EOT'
<div></div>
EOT;
    $fragments = Document::fromText($template)->getFragments();

    expect($fragments)->toHaveCount(2);

    /** @var HtmlFragment $fragmentOne */
    $fragmentOne = $fragments[0];
    expect($fragmentOne->tagName)->toBe('div');
    expect($fragmentOne->documentContent)->toBe('div');
    expect($fragmentOne->content)->toBe('<div>');
    expect($fragmentOne->isSelfClosing)->toBeFalse();
    expect($fragmentOne->isClosingTag)->toBeFalse();

    /** @var HtmlFragment $fragmentTwo */
    $fragmentTwo = $fragments[1];
    expect($fragmentTwo->tagName)->toBe('div');
    expect($fragmentTwo->documentContent)->toBe('div');
    expect($fragmentTwo->content)->toBe('</div>');
    expect($fragmentTwo->isSelfClosing)->toBeFalse();
    expect($fragmentTwo->isClosingTag)->toBeTrue();
});

test('self closing fragments', function () {
    $template = <<<'EOT'
<span v-text="title" />
EOT;
    $fragments = Document::fromText($template)->getFragments();

    expect($fragments)->toHaveCount(1);

    /** @var HtmlFragment $fragment */
    $fragment = $fragments[0];

    expect($fragment->isClosingTag)->toBeFalse();
    expect($fragment->isSelfClosing)->toBeTrue();

    expect($fragment->content)->toBe('<span v-text="title" />');
    expect($fragment->documentContent)->toBe('span v-text="title" ');
});

test('fragment positions', function () {
    $template = <<<'EOT'
<div>
    <span text="hello, world">Content</span>
</div>
EOT;

    /** @var HtmlFragment[] $fragments */
    $fragments = Document::fromText($template)->getFragments();
    expect($fragments)->toHaveCount(4);

    $f1 = $fragments[0];
    $this->assertFragmentPosition($f1, 1, 1, 1, 5);
    $this->assertFragmentNamePosition($f1, 1, 2, 1, 4);

    $f2 = $fragments[1];
    $this->assertFragmentPosition($f2, 2, 5, 2, 30);
    $this->assertFragmentNamePosition($f2, 2, 6, 2, 9);

    expect($f2->parameters)->toHaveCount(1);
    $p1 = $f2->parameters[0];
    expect($p1->content)->toBe('text="hello, world"');
    expect($p1->name)->toBe('text');
    expect($p1->value)->toBe('"hello, world"');
    expect($p1->type)->toBe(FragmentParameterType::Parameter);
    $this->assertFragmentParameterPosition($p1, 2, 11, 2, 29);

    $f3 = $fragments[2];
    $this->assertFragmentPosition($f3, 2, 38, 2, 44);
    $this->assertFragmentNamePosition($f3, 2, 40, 2, 43);

    $f4 = $fragments[3];
    $this->assertFragmentPosition($f4, 3, 1, 3, 6);
    $this->assertFragmentNamePosition($f4, 3, 3, 3, 5);
});

test('fragment parameters', function () {
    $template = <<<'EOT'
<input type="text" required />
EOT;

    /** @var HtmlFragment[] $fragments */
    $fragments = Document::fromText($template)->getFragments();

    expect($fragments)->toHaveCount(1);

    $f1 = $fragments[0];

    expect($f1->parameters)->toHaveCount(2);

    $p1 = $f1->parameters[0];
    expect($p1->content)->toBe('type="text"');
    expect($p1->name)->toBe('type');
    expect($p1->value)->toBe('"text"');
    expect($p1->getValue())->toBe('text');
    expect($f1->getParameter('type')->getValue())->toBe('text');
    expect($p1->type)->toBe(FragmentParameterType::Parameter);

    $p2 = $f1->parameters[1];
    expect($p2->content)->toBe('required');
    expect($p2->name)->toBe('required');
    expect($p2->value)->toBe('');
    expect($f1->getParameter('required')->getValue())->toBe('');
    expect($p2->type)->toBe(FragmentParameterType::Attribute);
});

test('fragment parameters if tag name ends with newline', function () {
    $template = <<<'EOT'
<input
type="text" required />
EOT;

    /** @var HtmlFragment[] $fragments */
    $fragments = Document::fromText($template)->getFragments();

    expect($fragments)->toHaveCount(1);

    $f1 = $fragments[0];

    expect($f1->parameters)->toHaveCount(2);

    $p1 = $f1->parameters[0];
    expect($p1->content)->toBe('type="text"');
    expect($p1->name)->toBe('type');
    expect($p1->value)->toBe('"text"');
    expect($p1->getValue())->toBe('text');
    expect($f1->getParameter('type')->getValue())->toBe('text');
    expect($p1->type)->toBe(FragmentParameterType::Parameter);

    $p2 = $f1->parameters[1];
    expect($p2->content)->toBe('required');
    expect($p2->name)->toBe('required');
    expect($p2->value)->toBe('');
    expect($f1->getParameter('required')->getValue())->toBe('');
    expect($p2->type)->toBe(FragmentParameterType::Attribute);
});

test('node fragment positions', function () {
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
    expect($if->fragmentPosition)->toBe(FragmentPosition::InsideParameter);

    $endIf = $directives[1];
    expect($endIf->fragmentPosition)->toBe(FragmentPosition::InsideParameter);

    /** @var EchoNode[] $echoes */
    $echoes = $doc->getEchoes();
    $varEcho = $echoes[0];
    expect($varEcho->fragmentPosition)->toBe(FragmentPosition::Unknown);

    $elementEcho = $echoes[1];
    expect($elementEcho->fragmentPosition)->toBe(FragmentPosition::InsideFragmentName);

    $attributesEcho = $echoes[2];
    expect($attributesEcho->fragmentPosition)->toBe(FragmentPosition::InsideFragment);

    $closeElement = $echoes[3];
    expect($closeElement->fragmentPosition)->toBe(FragmentPosition::InsideFragmentName);
});

test('fragment helper methods', function () {
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

    expect($doc->getNodes()->where(fn (AbstractNode $n) => $n->isInHtmlParameter()))->toHaveCount(2);
    expect($doc->getNodes()->where(fn (AbstractNode $n) => $n->isInHtmlTagName()))->toHaveCount(2);
    expect($doc->getNodes()->where(fn (AbstractNode $n) => $n->isInHtmlTagContent()))->toHaveCount(4);
    expect($doc->getNodes()->where(fn (AbstractNode $n) => $n->isBetweenHtmlFragments()))->toHaveCount(1);
});
