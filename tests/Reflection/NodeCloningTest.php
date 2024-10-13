<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Nodes\CommentNode;
use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Nodes\Components\ParameterAttribute;
use Stillat\BladeParser\Nodes\Components\ParameterNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\EchoNode;
use Stillat\BladeParser\Nodes\LiteralNode;
use Stillat\BladeParser\Nodes\PhpBlockNode;
use Stillat\BladeParser\Nodes\PhpTagNode;
use Stillat\BladeParser\Nodes\VerbatimNode;

test('literal cloning', function () {
    $template = <<<'EOT'
Just a literal
EOT;
    $document = $this->getDocument($template);

    /** @var LiteralNode $literal */
    $literal = $document->firstOfType(LiteralNode::class);
    $copy = $literal->clone();

    expect($literal)->toBeInstanceOf(LiteralNode::class);
    expect($copy)->toBeInstanceOf(LiteralNode::class);

    $this->assertClonedBasicDetailsMatch($copy, $literal);
    expect($literal->unescapedContent)->toBe($copy->unescapedContent);
});

test('comment node cloning', function () {
    $template = <<<'EOT'
{{-- Comment --}}
EOT;
    $document = $this->getDocument($template);

    /** @var CommentNode $comment */
    $comment = $document->firstOfType(CommentNode::class);
    $copy = $comment->clone();

    expect($comment)->toBeInstanceOf(CommentNode::class);
    expect($copy)->toBeInstanceOf(CommentNode::class);

    $this->assertClonedBasicDetailsMatch($copy, $comment);
    expect($comment->innerContent)->toBe($copy->innerContent);
});

test('directive node cloning', function () {
    $template = <<<'EOT'
@if ($something == $somethingElse)
EOT;
    $document = $this->getDocument($template);

    /** @var DirectiveNode $directive */
    $directive = $document->firstOfType(DirectiveNode::class);
    $copy = $directive->clone();

    expect($directive)->toBeInstanceOf(DirectiveNode::class);
    expect($copy)->toBeInstanceOf(DirectiveNode::class);

    $this->assertClonedBasicDetailsMatch($directive, $copy);
    expect($directive->sourceContent)->toBe($copy->sourceContent);

    $args = $directive->arguments;
    $argsCopy = $args->clone();

    $this->assertClonedBasicDetailsMatch($args, $argsCopy);
    expect($argsCopy->innerContent)->toBe($args->innerContent);
    expect($argsCopy->contentType)->toBe($args->contentType);
});

test('echo node cloning', function () {
    $template = <<<'EOT'
{{ $variable }}
EOT;
    $document = $this->getDocument($template);

    /** @var EchoNode $echo */
    $echo = $document->firstOfType(EchoNode::class);
    $copy = $echo->clone();

    expect($echo)->toBeInstanceOf(EchoNode::class);
    expect($copy)->toBeInstanceOf(EchoNode::class);

    $this->assertClonedBasicDetailsMatch($echo, $copy);
    expect($echo->innerContent)->toBe($copy->innerContent);
    expect($echo->type)->toBe($copy->type);
});

test('php block node cloning', function () {
    $template = <<<'EOT'
@php
    $justSomeContent = 'hello, world';
@endphp
EOT;
    $document = $this->getDocument($template);

    /** @var PhpBlockNode $phpBlock */
    $phpBlock = $document->firstOfType(PhpBlockNode::class);
    $copy = $phpBlock->clone();

    expect($phpBlock)->toBeInstanceOf(PhpBlockNode::class);
    expect($copy)->toBeInstanceOf(PhpBlockNode::class);

    $this->assertClonedBasicDetailsMatch($phpBlock, $copy);
    expect($copy->innerContent)->toBe($phpBlock->innerContent);
});

test('php tag node cloning', function () {
    $template = <<<'EOT'
<?php $justSomeContent = 'hello, world'; ?>
EOT;
    $document = $this->getDocument($template);

    /** @var PhpTagNode $phpTag */
    $phpTag = $document->firstOfType(PhpTagNode::class);
    $copy = $phpTag->clone();

    expect($phpTag)->toBeInstanceOf(PhpTagNode::class);
    expect($copy)->toBeInstanceOf(PhpTagNode::class);

    $this->assertClonedBasicDetailsMatch($phpTag, $copy);
    expect($copy->type)->toBe($phpTag->type);
});

test('raw echo node cloning', function () {
    $template = <<<'EOT'
{!! $variable !!}
EOT;
    $document = $this->getDocument($template);

    /** @var EchoNode $echo */
    $echo = $document->firstOfType(EchoNode::class);
    $copy = $echo->clone();

    expect($echo)->toBeInstanceOf(EchoNode::class);
    expect($copy)->toBeInstanceOf(EchoNode::class);

    $this->assertClonedBasicDetailsMatch($echo, $copy);
    expect($echo->innerContent)->toBe($copy->innerContent);
});

test('triple echo node cloning', function () {
    $template = <<<'EOT'
{{{ $variable }}}
EOT;
    $document = $this->getDocument($template);

    /** @var EchoNode $echo */
    $echo = $document->firstOfType(EchoNode::class);
    $copy = $echo->clone();

    expect($echo)->toBeInstanceOf(EchoNode::class);
    expect($copy)->toBeInstanceOf(EchoNode::class);

    $this->assertClonedBasicDetailsMatch($echo, $copy);
    expect($echo->innerContent)->toBe($copy->innerContent);
});

test('verbatim node cloning', function () {
    $template = <<<'EOT'
@verbatim
    @if @endif <?php ?>
    
    {{ echo }}
@endverbatim
EOT;
    $document = $this->getDocument($template);

    /** @var VerbatimNode $verbatim */
    $verbatim = $document->firstOfType(VerbatimNode::class);
    $copy = $verbatim->clone();

    expect($verbatim)->toBeInstanceOf(VerbatimNode::class);
    expect($copy)->toBeInstanceOf(VerbatimNode::class);

    $this->assertClonedBasicDetailsMatch($verbatim, $copy);
    expect($copy->innerContent)->toBe($verbatim->innerContent);
});

test('component node cloning', function () {
    $template = 'a<x-slot name="foo" />c';
    $document = $this->getDocument($template);

    /** @var ComponentNode $component */
    $component = $document->firstOfType(ComponentNode::class);
    $copy = $component->clone();

    expect($component)->toBeInstanceOf(ComponentNode::class);
    expect($copy)->toBeInstanceOf(ComponentNode::class);

    $this->assertClonedBasicDetailsMatch($component, $copy);
    expect($copy->isSelfClosing)->toBe($component->isSelfClosing);
    expect($copy->isClosingTag)->toBe($component->isClosingTag);
    expect($copy->innerContent)->toBe($component->innerContent);
    expect($copy->parameterContent)->toBe($component->parameterContent);
    expect($copy->name)->toBe($component->name);
    expect($copy->tagName)->toBe($component->tagName);

    $this->assertClonedPositionsMatch($component->namePosition, $copy->namePosition);
    $this->assertClonedPositionsMatch($component->parameterContentPosition, $copy->parameterContentPosition);

    expect($copy->parameterCount)->toBe($component->parameterCount);

    for ($i = 0; $i < count($component->parameters); $i++) {
        /** @var ParameterNode $parameter */
        $parameter = $component->parameters[$i];
        $paramClone = $parameter->clone();

        expect($parameter)->toBeInstanceOf(ParameterNode::class);
        expect($paramClone)->toBeInstanceOf(ParameterNode::class);

        $this->assertClonedBasicDetailsMatch($parameter, $paramClone);
        expect($paramClone->name)->toBe($parameter->name);
        expect($paramClone->materializedName)->toBe($parameter->materializedName);
        expect($paramClone->value)->toBe($parameter->value);

        /** @var ParameterAttribute $name */
        $name = $parameter->nameNode;
        $nameCopy = $name->clone();

        expect($name)->toBeInstanceOf(ParameterAttribute::class);
        expect($nameCopy)->toBeInstanceOf(ParameterAttribute::class);

        $this->assertClonedBasicDetailsMatch($name, $nameCopy);
        expect($nameCopy->content)->toBe($name->content);

        /** @var ParameterAttribute $value */
        $value = $parameter->valueNode;
        $valueCopy = $value->clone();

        expect($value)->toBeInstanceOf(ParameterAttribute::class);
        expect($valueCopy)->toBeInstanceOf(ParameterAttribute::class);

        $this->assertClonedBasicDetailsMatch($value, $valueCopy);
        expect($valueCopy->content)->toBe($value->content);
    }
});
