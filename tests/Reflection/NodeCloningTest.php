<?php

namespace Stillat\BladeParser\Tests\Reflection;

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
use Stillat\BladeParser\Tests\ParserTestCase;

class NodeCloningTest extends ParserTestCase
{
    public function testLiteralCloning()
    {
        $template = <<<'EOT'
Just a literal
EOT;
        $document = $this->getDocument($template);

        /** @var LiteralNode $literal */
        $literal = $document->firstOfType(LiteralNode::class);
        $copy = $literal->clone();

        $this->assertInstanceOf(LiteralNode::class, $literal);
        $this->assertInstanceOf(LiteralNode::class, $copy);

        $this->assertClonedBasicDetailsMatch($copy, $literal);
        $this->assertSame($copy->unescapedContent, $literal->unescapedContent);
    }

    public function testCommentNodeCloning()
    {
        $template = <<<'EOT'
{{-- Comment --}}
EOT;
        $document = $this->getDocument($template);

        /** @var CommentNode $comment */
        $comment = $document->firstOfType(CommentNode::class);
        $copy = $comment->clone();

        $this->assertInstanceOf(CommentNode::class, $comment);
        $this->assertInstanceOf(CommentNode::class, $copy);

        $this->assertClonedBasicDetailsMatch($copy, $comment);
        $this->assertSame($copy->innerContent, $comment->innerContent);
    }

    public function testDirectiveNodeCloning()
    {
        $template = <<<'EOT'
@if ($something == $somethingElse)
EOT;
        $document = $this->getDocument($template);

        /** @var DirectiveNode $directive */
        $directive = $document->firstOfType(DirectiveNode::class);
        $copy = $directive->clone();

        $this->assertInstanceOf(DirectiveNode::class, $directive);
        $this->assertInstanceOf(DirectiveNode::class, $copy);

        $this->assertClonedBasicDetailsMatch($directive, $copy);
        $this->assertSame($copy->sourceContent, $directive->sourceContent);

        $args = $directive->arguments;
        $argsCopy = $args->clone();

        $this->assertClonedBasicDetailsMatch($args, $argsCopy);
        $this->assertSame($args->innerContent, $argsCopy->innerContent);
        $this->assertSame($args->contentType, $argsCopy->contentType);
    }

    public function testEchoNodeCloning()
    {
        $template = <<<'EOT'
{{ $variable }}
EOT;
        $document = $this->getDocument($template);

        /** @var EchoNode $echo */
        $echo = $document->firstOfType(EchoNode::class);
        $copy = $echo->clone();

        $this->assertInstanceOf(EchoNode::class, $echo);
        $this->assertInstanceOf(EchoNode::class, $copy);

        $this->assertClonedBasicDetailsMatch($echo, $copy);
        $this->assertSame($copy->innerContent, $echo->innerContent);
        $this->assertSame($copy->type, $echo->type);
    }

    public function testPhpBlockNodeCloning()
    {
        $template = <<<'EOT'
@php
    $justSomeContent = 'hello, world';
@endphp
EOT;
        $document = $this->getDocument($template);

        /** @var PhpBlockNode $phpBlock */
        $phpBlock = $document->firstOfType(PhpBlockNode::class);
        $copy = $phpBlock->clone();

        $this->assertInstanceOf(PhpBlockNode::class, $phpBlock);
        $this->assertInstanceOf(PhpBlockNode::class, $copy);

        $this->assertClonedBasicDetailsMatch($phpBlock, $copy);
        $this->assertSame($phpBlock->innerContent, $copy->innerContent);
    }

    public function testPhpTagNodeCloning()
    {
        $template = <<<'EOT'
<?php $justSomeContent = 'hello, world'; ?>
EOT;
        $document = $this->getDocument($template);

        /** @var PhpTagNode $phpTag */
        $phpTag = $document->firstOfType(PhpTagNode::class);
        $copy = $phpTag->clone();

        $this->assertInstanceOf(PhpTagNode::class, $phpTag);
        $this->assertInstanceOf(PhpTagNode::class, $copy);

        $this->assertClonedBasicDetailsMatch($phpTag, $copy);
        $this->assertSame($phpTag->type, $copy->type);
    }

    public function testRawEchoNodeCloning()
    {
        $template = <<<'EOT'
{!! $variable !!}
EOT;
        $document = $this->getDocument($template);

        /** @var EchoNode $echo */
        $echo = $document->firstOfType(EchoNode::class);
        $copy = $echo->clone();

        $this->assertInstanceOf(EchoNode::class, $echo);
        $this->assertInstanceOf(EchoNode::class, $copy);

        $this->assertClonedBasicDetailsMatch($echo, $copy);
        $this->assertSame($copy->innerContent, $echo->innerContent);
    }

    public function testTripleEchoNodeCloning()
    {
        $template = <<<'EOT'
{{{ $variable }}}
EOT;
        $document = $this->getDocument($template);

        /** @var EchoNode $echo */
        $echo = $document->firstOfType(EchoNode::class);
        $copy = $echo->clone();

        $this->assertInstanceOf(EchoNode::class, $echo);
        $this->assertInstanceOf(EchoNode::class, $copy);

        $this->assertClonedBasicDetailsMatch($echo, $copy);
        $this->assertSame($copy->innerContent, $echo->innerContent);
    }

    public function testVerbatimNodeCloning()
    {
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

        $this->assertInstanceOf(VerbatimNode::class, $verbatim);
        $this->assertInstanceOf(VerbatimNode::class, $copy);

        $this->assertClonedBasicDetailsMatch($verbatim, $copy);
        $this->assertSame($verbatim->innerContent, $copy->innerContent);
    }

    public function testComponentNodeCloning()
    {
        $template = 'a<x-slot name="foo" />c';
        $document = $this->getDocument($template);

        /** @var ComponentNode $component */
        $component = $document->firstOfType(ComponentNode::class);
        $copy = $component->clone();

        $this->assertInstanceOf(ComponentNode::class, $component);
        $this->assertInstanceOf(ComponentNode::class, $copy);

        $this->assertClonedBasicDetailsMatch($component, $copy);
        $this->assertSame($component->isSelfClosing, $copy->isSelfClosing);
        $this->assertSame($component->isClosingTag, $copy->isClosingTag);
        $this->assertSame($component->innerContent, $copy->innerContent);
        $this->assertSame($component->parameterContent, $copy->parameterContent);
        $this->assertSame($component->name, $copy->name);
        $this->assertSame($component->tagName, $copy->tagName);

        $this->assertClonedPositionsMatch($component->namePosition, $copy->namePosition);
        $this->assertClonedPositionsMatch($component->parameterContentPosition, $copy->parameterContentPosition);

        $this->assertSame($component->parameterCount, $copy->parameterCount);

        for ($i = 0; $i < count($component->parameters); $i++) {
            /** @var ParameterNode $parameter */
            $parameter = $component->parameters[$i];
            $paramClone = $parameter->clone();

            $this->assertInstanceOf(ParameterNode::class, $parameter);
            $this->assertInstanceOf(ParameterNode::class, $paramClone);

            $this->assertClonedBasicDetailsMatch($parameter, $paramClone);
            $this->assertSame($parameter->name, $paramClone->name);
            $this->assertSame($parameter->materializedName, $paramClone->materializedName);
            $this->assertSame($parameter->value, $paramClone->value);

            /** @var ParameterAttribute $name */
            $name = $parameter->nameNode;
            $nameCopy = $name->clone();

            $this->assertInstanceOf(ParameterAttribute::class, $name);
            $this->assertInstanceOf(ParameterAttribute::class, $nameCopy);

            $this->assertClonedBasicDetailsMatch($name, $nameCopy);
            $this->assertSame($name->content, $nameCopy->content);

            /** @var ParameterAttribute $value */
            $value = $parameter->valueNode;
            $valueCopy = $value->clone();

            $this->assertInstanceOf(ParameterAttribute::class, $value);
            $this->assertInstanceOf(ParameterAttribute::class, $valueCopy);

            $this->assertClonedBasicDetailsMatch($value, $valueCopy);
            $this->assertSame($value->content, $valueCopy->content);
        }
    }
}
