<?php

namespace Stillat\BladeParser\Tests\Parser;

use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Nodes\Components\ParameterNode;
use Stillat\BladeParser\Nodes\Components\ParameterType;
use Stillat\BladeParser\Nodes\LiteralNode;
use Stillat\BladeParser\Tests\ParserTestCase;

class TagComponentsTest extends ParserTestCase
{
    public function testBasicTagComponentOpening()
    {
        $template = '<x-slot name="foo">';
        $nodes = $this->parseNodes($template);
        $this->assertCount(1, $nodes);

        /** @var ComponentNode $component */
        $component = $nodes[0];

        $this->assertInstanceOf(ComponentNode::class, $component);

        $this->assertSame($template, $component->content);
        $this->assertSame('slot name="foo"', $component->innerContent);
        $this->assertSame(false, $component->isClosingTag);
        $this->assertSame(false, $component->isSelfClosing);
    }

    public function testBasicTagComponentClosing()
    {
        $template = '</x-slot>';
        $nodes = $this->parseNodes($template);
        $this->assertCount(1, $nodes);

        /** @var ComponentNode $component */
        $component = $nodes[0];

        $this->assertInstanceOf(ComponentNode::class, $component);

        $this->assertSame($template, $component->content);
        $this->assertSame('slot', $component->innerContent);

        $this->assertSame(false, $component->isSelfClosing);
        $this->assertSame(true, $component->isClosingTag);
    }

    public function testBasicTagComponentSelfClosing()
    {
        $template = '<x-slot name="foo" />';
        $nodes = $this->parseNodes($template);
        $this->assertCount(1, $nodes);

        /** @var ComponentNode $component */
        $component = $nodes[0];

        $this->assertInstanceOf(ComponentNode::class, $component);

        $this->assertSame($template, $component->content);
        $this->assertSame('slot name="foo" ', $component->innerContent);
        $this->assertSame(true, $component->isSelfClosing);
        $this->assertSame(true, $component->isClosingTag);
    }

    public function testComponentTagsDoNotConsumeLiteralContent()
    {
        $template = 'a<x-slot name="foo">b</x-slot>c';
        $nodes = $this->parseNodes($template);
        $this->assertCount(5, $nodes);

        $this->assertLiteralContent($nodes[0], 'a');
        $this->assertLiteralContent($nodes[2], 'b');
        $this->assertLiteralContent($nodes[4], 'c');

        $this->assertInstanceOf(ComponentNode::class, $nodes[1]);
        $this->assertInstanceOf(ComponentNode::class, $nodes[3]);

        /** @var ComponentNode $componentOne */
        $componentOne = $nodes[1];

        $this->assertSame('slot name="foo"', $componentOne->innerContent);
        $this->assertSame(false, $componentOne->isClosingTag);
        $this->assertSame(false, $componentOne->isSelfClosing);
        $this->assertSame('<x-slot name="foo">', $componentOne->content);

        /** @var ComponentNode $componentTwo */
        $componentTwo = $nodes[3];

        $this->assertSame('slot', $componentTwo->innerContent);
        $this->assertSame(true, $componentTwo->isClosingTag);
        $this->assertSame(false, $componentTwo->isSelfClosing);
        $this->assertSame('</x-slot>', $componentTwo->content);
    }

    public function testBasicComponentParameters()
    {
        $template = 'a<x-slot name = "foo" another="one" attribute  some_parameter="value">b</x-slot>c';
        $nodes = $this->parseNodes($template);
        $this->assertCount(5, $nodes);

        $this->assertLiteralContent($nodes[0], 'a');
        $this->assertLiteralContent($nodes[2], 'b');
        $this->assertLiteralContent($nodes[4], 'c');

        $this->assertInstanceOf(ComponentNode::class, $nodes[1]);
        $this->assertInstanceOf(ComponentNode::class, $nodes[3]);

        /** @var ComponentNode $componentOne */
        $componentOne = $nodes[1];
        $this->assertSame(4, $componentOne->parameterCount);
        $this->assertCount(4, $componentOne->parameters);
        $this->assertTrue($componentOne->hasParameters());
        $this->assertSame('slot name = "foo" another="one" attribute  some_parameter="value"', $componentOne->innerContent);
        $this->assertSame(false, $componentOne->isClosingTag);
        $this->assertSame(false, $componentOne->isSelfClosing);
        $this->assertSame('<x-slot name = "foo" another="one" attribute  some_parameter="value">', $componentOne->content);
        $this->assertSame(' name = "foo" another="one" attribute  some_parameter="value"', $componentOne->parameterContent);

        $this->assertInstanceOf(ParameterNode::class, $componentOne->parameters[0]);
        $this->assertInstanceOf(ParameterNode::class, $componentOne->parameters[1]);
        $this->assertInstanceOf(ParameterNode::class, $componentOne->parameters[2]);
        $this->assertInstanceOf(ParameterNode::class, $componentOne->parameters[3]);

        $paramOne = $componentOne->parameters[0];
        $this->assertSame('name = "foo"', $paramOne->content);
        $this->assertNotNull($paramOne->nameNode);
        $this->assertNotNull($paramOne->valueNode);
        $this->assertNotNull($paramOne->position);
        $this->assertSame(8, $paramOne->position->startOffset);
        $this->assertSame(19, $paramOne->position->endOffset);
        $this->assertSame('name', $paramOne->name);
        $this->assertSame('foo', $paramOne->value);

        $this->assertSame('name', $paramOne->nameNode->content);
        $this->assertNotNull($paramOne->nameNode->position);
        $this->assertSame(8, $paramOne->nameNode->position->startOffset);
        $this->assertSame(11, $paramOne->nameNode->position->endOffset);

        $this->assertSame('"foo"', $paramOne->valueNode->content);
        $this->assertNotNull($paramOne->valueNode->position);
        $this->assertSame(15, $paramOne->valueNode->position->startOffset);
        $this->assertSame(19, $paramOne->valueNode->position->endOffset);
        $this->assertSame(ParameterType::Parameter, $paramOne->type);

        $paramTwo = $componentOne->parameters[1];
        $this->assertSame('another="one"', $paramTwo->content);
        $this->assertNotNull($paramTwo->nameNode);
        $this->assertNotNull($paramTwo->valueNode);
        $this->assertNotNull($paramTwo->position);
        $this->assertSame(21, $paramTwo->position->startOffset);
        $this->assertSame(33, $paramTwo->position->endOffset);
        $this->assertSame('another', $paramTwo->name);
        $this->assertSame('one', $paramTwo->value);

        $this->assertSame('another', $paramTwo->nameNode->content);
        $this->assertNotNull($paramTwo->nameNode->position);
        $this->assertSame(21, $paramTwo->nameNode->position->startOffset);
        $this->assertSame(27, $paramTwo->nameNode->position->endOffset);

        $this->assertSame('"one"', $paramTwo->valueNode->content);
        $this->assertNotNull($paramTwo->valueNode->position);
        $this->assertSame(29, $paramTwo->valueNode->position->startOffset);
        $this->assertSame(33, $paramTwo->valueNode->position->endOffset);
        $this->assertSame(ParameterType::Parameter, $paramTwo->type);

        $paramThree = $componentOne->parameters[2];
        $this->assertSame('attribute', $paramThree->content);
        $this->assertNotNull($paramThree->nameNode);
        $this->assertNull($paramThree->valueNode);
        $this->assertNotNull($paramThree->position);
        $this->assertSame(35, $paramThree->position->startOffset);
        $this->assertSame(43, $paramThree->position->endOffset);
        $this->assertSame('attribute', $paramThree->name);
        $this->assertSame('', $paramThree->value);
        $this->assertSame('attribute', $paramThree->nameNode->content);
        $this->assertNotNull($paramThree->nameNode->position);
        $this->assertSame(35, $paramThree->nameNode->position->startOffset);
        $this->assertSame(43, $paramThree->nameNode->position->endOffset);
        $this->assertSame(ParameterType::Attribute, $paramThree->type);

        $paramFour = $componentOne->parameters[3];
        $this->assertSame('some_parameter="value"', $paramFour->content);
        $this->assertNotNull($paramFour->nameNode);
        $this->assertNotNull($paramFour->valueNode);
        $this->assertNotNull($paramFour->position);
        $this->assertSame(46, $paramFour->position->startOffset);
        $this->assertSame(67, $paramFour->position->endOffset);
        $this->assertSame('some_parameter', $paramFour->name);
        $this->assertSame('value', $paramFour->value);

        $this->assertSame('some_parameter', $paramFour->nameNode->content);
        $this->assertNotNull($paramFour->nameNode->position);
        $this->assertSame(46, $paramFour->nameNode->position->startOffset);
        $this->assertSame(59, $paramFour->nameNode->position->endOffset);

        $this->assertSame('"value"', $paramFour->valueNode->content);
        $this->assertNotNull($paramFour->valueNode->position);
        $this->assertSame(61, $paramFour->valueNode->position->startOffset);
        $this->assertSame(67, $paramFour->valueNode->position->endOffset);

        /** @var ComponentNode $componentTwo */
        $componentTwo = $nodes[3];

        $this->assertSame(0, $componentTwo->parameterCount);
        $this->assertFalse($componentTwo->hasParameters());
        $this->assertNotNull($componentTwo->namePosition);
        $this->assertNotNull($componentTwo->position);
        $this->assertSame(71, $componentTwo->position->startOffset);
        $this->assertSame(79, $componentTwo->position->endOffset);

        $this->assertSame(74, $componentTwo->namePosition->startOffset);
        $this->assertSame(77, $componentTwo->namePosition->endOffset);

        $this->assertSame('slot', $componentTwo->innerContent);
        $this->assertSame(true, $componentTwo->isClosingTag);
        $this->assertSame(false, $componentTwo->isSelfClosing);
        $this->assertSame('</x-slot>', $componentTwo->content);
    }

    public function testParametersSpanningMultipleLines()
    {
        $template = <<<'EOT'
a<x-slot name =
    "foo">b</x-slot>c
EOT;
        $nodes = $this->parseNodes($template);
        $this->assertCount(5, $nodes);
        $this->assertLiteralContent($nodes[0], 'a');
        $this->assertLiteralContent($nodes[2], 'b');
        $this->assertLiteralContent($nodes[4], 'c');

        $this->assertInstanceOf(ComponentNode::class, $nodes[1]);
        $this->assertInstanceOf(ComponentNode::class, $nodes[3]);

        /** @var ComponentNode $componentOne */
        $component = $nodes[1];
        $this->assertSame(1, $component->parameterCount);
        $this->assertTrue($component->hasParameters());
        $this->assertNotNull($component->namePosition);
        $this->assertNotNull($component->position);
        $this->assertSame(1, $component->position->startOffset);
        $this->assertSame(25, $component->position->endOffset);
        $this->assertSame(3, $component->namePosition->startOffset);
        $this->assertSame(6, $component->namePosition->endOffset);
        $this->assertSame('slot', $component->name);

        $this->assertInstanceOf(ParameterNode::class, $component->parameters[0]);

        $paramOne = $component->parameters[0];
        $this->assertSame('name', $paramOne->name);
        $this->assertSame('foo', $paramOne->value);
        $this->assertNotNull($paramOne->nameNode);
        $this->assertNotNull($paramOne->valueNode);
        $this->assertNotNull($paramOne->position);
        $this->assertSame(8, $paramOne->position->startOffset);
        $this->assertSame(23, $paramOne->position->endOffset);

        $this->assertSame('name', $paramOne->nameNode->content);
        $this->assertNotNull($paramOne->nameNode->position);
        $this->assertSame(8, $paramOne->nameNode->position->startOffset);
        $this->assertSame(11, $paramOne->nameNode->position->endOffset);

        $this->assertSame('"foo"', $paramOne->valueNode->content);
        $this->assertNotNull($paramOne->valueNode->position);
        $this->assertSame(19, $paramOne->valueNode->position->startOffset);
        $this->assertSame(23, $paramOne->valueNode->position->endOffset);
    }

    public function testComponentTagsWithSemicolon()
    {
        $template = <<<'EOT'
<x-slot:foo>
EOT;

        $nodes = $this->parseNodes($template);
        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(ComponentNode::class, $nodes[0]);

        /** @var ComponentNode $component */
        $component = $nodes[0];

        $this->assertSame('slot:foo', $component->name);
        $this->assertSame('slot', $component->tagName);
    }

    public function testVariableParameterTypes()
    {
        $template = <<<'EOT'
<x-component :messages="$messages" :$userId />
EOT;
        $nodes = $this->parseNodes($template);
        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(ComponentNode::class, $nodes[0]);

        /** @var ComponentNode $component */
        $component = $nodes[0];

        $this->assertSame(2, $component->parameterCount);
        $this->assertTrue($component->hasParameters());

        $paramOne = $component->parameters[0];
        $this->assertSame(':messages', $paramOne->name);
        $this->assertSame('messages', $paramOne->materializedName);
        $this->assertSame('$messages', $paramOne->value);
        $this->assertSame(ParameterType::DynamicVariable, $paramOne->type);

        $paramTwo = $component->parameters[1];
        $this->assertSame(':$userId', $paramTwo->name);
        $this->assertSame('user-id', $paramTwo->materializedName);
        $this->assertSame('$userId', $paramTwo->value);

        $template = <<<'EOT'
<x-component :messages="$messages":$userId />
EOT;
        $nodes = $this->parseNodes($template);
        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(ComponentNode::class, $nodes[0]);

        /** @var ComponentNode $component */
        $component = $nodes[0];

        $this->assertSame(2, $component->parameterCount);
        $this->assertTrue($component->hasParameters());

        $paramOne = $component->parameters[0];
        $this->assertSame(':messages', $paramOne->name);
        $this->assertSame('messages', $paramOne->materializedName);
        $this->assertSame('$messages', $paramOne->value);
        $this->assertSame(ParameterType::DynamicVariable, $paramOne->type);

        $paramTwo = $component->parameters[1];
        $this->assertSame(':$userId', $paramTwo->name);
        $this->assertSame('user-id', $paramTwo->materializedName);
        $this->assertSame('$userId', $paramTwo->value);

        $template = <<<'EOT'
<x-component 
        :messages="$messages":$userId/>
EOT;
        $nodes = $this->parseNodes($template);
        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(ComponentNode::class, $nodes[0]);

        /** @var ComponentNode $component */
        $component = $nodes[0];

        $this->assertSame(2, $component->parameterCount);
        $this->assertTrue($component->hasParameters());

        $paramOne = $component->parameters[0];
        $this->assertSame(':messages', $paramOne->name);
        $this->assertSame('messages', $paramOne->materializedName);
        $this->assertSame('$messages', $paramOne->value);
        $this->assertSame(ParameterType::DynamicVariable, $paramOne->type);

        $paramTwo = $component->parameters[1];
        $this->assertSame(':$userId', $paramTwo->name);
        $this->assertSame('user-id', $paramTwo->materializedName);
        $this->assertSame('$userId', $paramTwo->value);

        $template = <<<'EOT'
<x-component 
        :messages="$messages"
        :$userId                        />
EOT;
        $nodes = $this->parseNodes($template);
        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(ComponentNode::class, $nodes[0]);

        /** @var ComponentNode $component */
        $component = $nodes[0];

        $this->assertSame(2, $component->parameterCount);
        $this->assertTrue($component->hasParameters());

        $paramOne = $component->parameters[0];
        $this->assertSame(':messages', $paramOne->name);
        $this->assertSame('messages', $paramOne->materializedName);
        $this->assertSame('$messages', $paramOne->value);
        $this->assertSame(ParameterType::DynamicVariable, $paramOne->type);

        $paramTwo = $component->parameters[1];
        $this->assertSame(':$userId', $paramTwo->name);
        $this->assertSame('user-id', $paramTwo->materializedName);
        $this->assertSame('$userId', $paramTwo->value);
    }

    public function testInterpolatedParametersAreDetected()
    {
        $template = <<<'EOT'
<x-button @click="update('{{ $name }}')" />
EOT;
        $nodes = $this->parseNodes($template);
        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(ComponentNode::class, $nodes[0]);

        /** @var ComponentNode $component */
        $component = $nodes[0];
        $this->assertCount(1, $component->parameters);

        $paramOne = $component->parameters[0];

        $this->assertSame('@click', $paramOne->name);
        $this->assertSame(ParameterType::InterpolatedValue, $paramOne->type);
    }

    public function testEscapedParametersAreDetected()
    {
        $template = <<<'EOT'
<x-button ::class="{{ something }}" />
EOT;
        $nodes = $this->parseNodes($template);
        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(ComponentNode::class, $nodes[0]);

        /** @var ComponentNode $component */
        $component = $nodes[0];
        $this->assertCount(1, $component->parameters);

        $paramOne = $component->parameters[0];

        $this->assertSame('::class', $paramOne->name);
        $this->assertSame(':class', $paramOne->materializedName);
        $this->assertSame(ParameterType::EscapedParameter, $paramOne->type);
    }

    public function testParserCanBeConfiguredToOnlyParseComponents()
    {
        $template = <<<'EOT'
<x-alert>
    {{ $title }} @if ($this) @endif
</x-alert>
EOT;
        $nodes = $this->parser()->onlyParseComponents()->parse($template);

        $this->assertCount(3, $nodes);
        $this->assertInstanceOf(ComponentNode::class, $nodes[0]);
        $this->assertInstanceOf(LiteralNode::class, $nodes[1]);
        $this->assertInstanceOf(ComponentNode::class, $nodes[2]);
    }
}
