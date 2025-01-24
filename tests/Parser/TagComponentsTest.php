<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Nodes\Components\ParameterNode;
use Stillat\BladeParser\Nodes\Components\ParameterType;
use Stillat\BladeParser\Nodes\LiteralNode;

test('basic tag component opening', function () {
    $template = '<x-slot name="foo">';
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(1);

    /** @var ComponentNode $component */
    $component = $nodes[0];

    expect($component)->toBeInstanceOf(ComponentNode::class);

    expect($component->content)->toBe($template);
    expect($component->innerContent)->toBe('slot name="foo"');
    expect($component->isClosingTag)->toBe(false);
    expect($component->isSelfClosing)->toBe(false);
});

test('basic tag component closing', function () {
    $template = '</x-slot>';
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(1);

    /** @var ComponentNode $component */
    $component = $nodes[0];

    expect($component)->toBeInstanceOf(ComponentNode::class);

    expect($component->content)->toBe($template);
    expect($component->innerContent)->toBe('slot');

    expect($component->isSelfClosing)->toBe(false);
    expect($component->isClosingTag)->toBe(true);
});

test('basic tag component self closing', function () {
    $template = '<x-slot name="foo" />';
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(1);

    /** @var ComponentNode $component */
    $component = $nodes[0];

    expect($component)->toBeInstanceOf(ComponentNode::class);

    expect($component->content)->toBe($template);
    expect($component->innerContent)->toBe('slot name="foo" ');
    expect($component->isSelfClosing)->toBe(true);
    expect($component->isClosingTag)->toBe(true);
});

test('component tags do not consume literal content', function () {
    $template = 'a<x-slot name="foo">b</x-slot>c';
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(5);

    $this->assertLiteralContent($nodes[0], 'a');
    $this->assertLiteralContent($nodes[2], 'b');
    $this->assertLiteralContent($nodes[4], 'c');

    expect($nodes[1])->toBeInstanceOf(ComponentNode::class);
    expect($nodes[3])->toBeInstanceOf(ComponentNode::class);

    /** @var ComponentNode $componentOne */
    $componentOne = $nodes[1];

    expect($componentOne->innerContent)->toBe('slot name="foo"');
    expect($componentOne->isClosingTag)->toBe(false);
    expect($componentOne->isSelfClosing)->toBe(false);
    expect($componentOne->content)->toBe('<x-slot name="foo">');

    /** @var ComponentNode $componentTwo */
    $componentTwo = $nodes[3];

    expect($componentTwo->innerContent)->toBe('slot');
    expect($componentTwo->isClosingTag)->toBe(true);
    expect($componentTwo->isSelfClosing)->toBe(false);
    expect($componentTwo->content)->toBe('</x-slot>');
});

test('basic component parameters', function () {
    $template = 'a<x-slot name = "foo" another="one" attribute  some_parameter="value">b</x-slot>c';
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(5);

    $this->assertLiteralContent($nodes[0], 'a');
    $this->assertLiteralContent($nodes[2], 'b');
    $this->assertLiteralContent($nodes[4], 'c');

    expect($nodes[1])->toBeInstanceOf(ComponentNode::class);
    expect($nodes[3])->toBeInstanceOf(ComponentNode::class);

    /** @var ComponentNode $componentOne */
    $componentOne = $nodes[1];
    expect($componentOne->parameterCount)->toBe(4);
    expect($componentOne->parameters)->toHaveCount(4);
    expect($componentOne->hasParameters())->toBeTrue();
    expect($componentOne->innerContent)->toBe('slot name = "foo" another="one" attribute  some_parameter="value"');
    expect($componentOne->isClosingTag)->toBe(false);
    expect($componentOne->isSelfClosing)->toBe(false);
    expect($componentOne->content)->toBe('<x-slot name = "foo" another="one" attribute  some_parameter="value">');
    expect($componentOne->parameterContent)->toBe(' name = "foo" another="one" attribute  some_parameter="value"');

    expect($componentOne->parameters[0])->toBeInstanceOf(ParameterNode::class);
    expect($componentOne->parameters[1])->toBeInstanceOf(ParameterNode::class);
    expect($componentOne->parameters[2])->toBeInstanceOf(ParameterNode::class);
    expect($componentOne->parameters[3])->toBeInstanceOf(ParameterNode::class);

    $paramOne = $componentOne->parameters[0];
    expect($paramOne->content)->toBe('name = "foo"');
    expect($paramOne->nameNode)->not->toBeNull();
    expect($paramOne->valueNode)->not->toBeNull();
    expect($paramOne->position)->not->toBeNull();
    expect($paramOne->position->startOffset)->toBe(8);
    expect($paramOne->position->endOffset)->toBe(19);
    expect($paramOne->name)->toBe('name');
    expect($paramOne->value)->toBe('foo');

    expect($paramOne->nameNode->content)->toBe('name');
    expect($paramOne->nameNode->position)->not->toBeNull();
    expect($paramOne->nameNode->position->startOffset)->toBe(8);
    expect($paramOne->nameNode->position->endOffset)->toBe(11);

    expect($paramOne->valueNode->content)->toBe('"foo"');
    expect($paramOne->valueNode->position)->not->toBeNull();
    expect($paramOne->valueNode->position->startOffset)->toBe(15);
    expect($paramOne->valueNode->position->endOffset)->toBe(19);
    expect($paramOne->type)->toBe(ParameterType::Parameter);

    $paramTwo = $componentOne->parameters[1];
    expect($paramTwo->content)->toBe('another="one"');
    expect($paramTwo->nameNode)->not->toBeNull();
    expect($paramTwo->valueNode)->not->toBeNull();
    expect($paramTwo->position)->not->toBeNull();
    expect($paramTwo->position->startOffset)->toBe(21);
    expect($paramTwo->position->endOffset)->toBe(33);
    expect($paramTwo->name)->toBe('another');
    expect($paramTwo->value)->toBe('one');

    expect($paramTwo->nameNode->content)->toBe('another');
    expect($paramTwo->nameNode->position)->not->toBeNull();
    expect($paramTwo->nameNode->position->startOffset)->toBe(21);
    expect($paramTwo->nameNode->position->endOffset)->toBe(27);

    expect($paramTwo->valueNode->content)->toBe('"one"');
    expect($paramTwo->valueNode->position)->not->toBeNull();
    expect($paramTwo->valueNode->position->startOffset)->toBe(29);
    expect($paramTwo->valueNode->position->endOffset)->toBe(33);
    expect($paramTwo->type)->toBe(ParameterType::Parameter);

    $paramThree = $componentOne->parameters[2];
    expect($paramThree->content)->toBe('attribute');
    expect($paramThree->nameNode)->not->toBeNull();
    expect($paramThree->valueNode)->toBeNull();
    expect($paramThree->position)->not->toBeNull();
    expect($paramThree->position->startOffset)->toBe(35);
    expect($paramThree->position->endOffset)->toBe(43);
    expect($paramThree->name)->toBe('attribute');
    expect($paramThree->value)->toBe('');
    expect($paramThree->nameNode->content)->toBe('attribute');
    expect($paramThree->nameNode->position)->not->toBeNull();
    expect($paramThree->nameNode->position->startOffset)->toBe(35);
    expect($paramThree->nameNode->position->endOffset)->toBe(43);
    expect($paramThree->type)->toBe(ParameterType::Attribute);

    $paramFour = $componentOne->parameters[3];
    expect($paramFour->content)->toBe('some_parameter="value"');
    expect($paramFour->nameNode)->not->toBeNull();
    expect($paramFour->valueNode)->not->toBeNull();
    expect($paramFour->position)->not->toBeNull();
    expect($paramFour->position->startOffset)->toBe(46);
    expect($paramFour->position->endOffset)->toBe(67);
    expect($paramFour->name)->toBe('some_parameter');
    expect($paramFour->value)->toBe('value');

    expect($paramFour->nameNode->content)->toBe('some_parameter');
    expect($paramFour->nameNode->position)->not->toBeNull();
    expect($paramFour->nameNode->position->startOffset)->toBe(46);
    expect($paramFour->nameNode->position->endOffset)->toBe(59);

    expect($paramFour->valueNode->content)->toBe('"value"');
    expect($paramFour->valueNode->position)->not->toBeNull();
    expect($paramFour->valueNode->position->startOffset)->toBe(61);
    expect($paramFour->valueNode->position->endOffset)->toBe(67);

    /** @var ComponentNode $componentTwo */
    $componentTwo = $nodes[3];

    expect($componentTwo->parameterCount)->toBe(0);
    expect($componentTwo->hasParameters())->toBeFalse();
    expect($componentTwo->namePosition)->not->toBeNull();
    expect($componentTwo->position)->not->toBeNull();
    expect($componentTwo->position->startOffset)->toBe(71);
    expect($componentTwo->position->endOffset)->toBe(79);

    expect($componentTwo->namePosition->startOffset)->toBe(74);
    expect($componentTwo->namePosition->endOffset)->toBe(77);

    expect($componentTwo->innerContent)->toBe('slot');
    expect($componentTwo->isClosingTag)->toBe(true);
    expect($componentTwo->isSelfClosing)->toBe(false);
    expect($componentTwo->content)->toBe('</x-slot>');
});

test('parameters spanning multiple lines', function () {
    $template = <<<'EOT'
a<x-slot name =
    "foo">b</x-slot>c
EOT;
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(5);
    $this->assertLiteralContent($nodes[0], 'a');
    $this->assertLiteralContent($nodes[2], 'b');
    $this->assertLiteralContent($nodes[4], 'c');

    expect($nodes[1])->toBeInstanceOf(ComponentNode::class);
    expect($nodes[3])->toBeInstanceOf(ComponentNode::class);

    /** @var ComponentNode $componentOne */
    $component = $nodes[1];
    expect($component->parameterCount)->toBe(1);
    expect($component->hasParameters())->toBeTrue();
    expect($component->namePosition)->not->toBeNull();
    expect($component->position)->not->toBeNull();
    expect($component->position->startOffset)->toBe(1);
    expect($component->position->endOffset)->toBe(25);
    expect($component->namePosition->startOffset)->toBe(3);
    expect($component->namePosition->endOffset)->toBe(6);
    expect($component->name)->toBe('slot');

    expect($component->parameters[0])->toBeInstanceOf(ParameterNode::class);

    $paramOne = $component->parameters[0];
    expect($paramOne->name)->toBe('name');
    expect($paramOne->value)->toBe('foo');
    expect($paramOne->nameNode)->not->toBeNull();
    expect($paramOne->valueNode)->not->toBeNull();
    expect($paramOne->position)->not->toBeNull();
    expect($paramOne->position->startOffset)->toBe(8);
    expect($paramOne->position->endOffset)->toBe(23);

    expect($paramOne->nameNode->content)->toBe('name');
    expect($paramOne->nameNode->position)->not->toBeNull();
    expect($paramOne->nameNode->position->startOffset)->toBe(8);
    expect($paramOne->nameNode->position->endOffset)->toBe(11);

    expect($paramOne->valueNode->content)->toBe('"foo"');
    expect($paramOne->valueNode->position)->not->toBeNull();
    expect($paramOne->valueNode->position->startOffset)->toBe(19);
    expect($paramOne->valueNode->position->endOffset)->toBe(23);
});

test('component tags with semicolon', function () {
    $template = <<<'EOT'
<x-slot:foo>
EOT;

    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(1);
    expect($nodes[0])->toBeInstanceOf(ComponentNode::class);

    /** @var ComponentNode $component */
    $component = $nodes[0];

    expect($component->name)->toBe('slot:foo');
    expect($component->tagName)->toBe('slot');
});

test('variable parameter types', function () {
    $template = <<<'EOT'
<x-component :messages="$messages" :$userId />
EOT;
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(1);
    expect($nodes[0])->toBeInstanceOf(ComponentNode::class);

    /** @var ComponentNode $component */
    $component = $nodes[0];

    expect($component->parameterCount)->toBe(2);
    expect($component->hasParameters())->toBeTrue();

    $paramOne = $component->parameters[0];
    expect($paramOne->name)->toBe(':messages');
    expect($paramOne->materializedName)->toBe('messages');
    expect($paramOne->value)->toBe('$messages');
    expect($paramOne->type)->toBe(ParameterType::DynamicVariable);

    $paramTwo = $component->parameters[1];
    expect($paramTwo->name)->toBe(':$userId');
    expect($paramTwo->materializedName)->toBe('user-id');
    expect($paramTwo->value)->toBe('$userId');

    $template = <<<'EOT'
<x-component :messages="$messages":$userId />
EOT;
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(1);
    expect($nodes[0])->toBeInstanceOf(ComponentNode::class);

    /** @var ComponentNode $component */
    $component = $nodes[0];

    expect($component->parameterCount)->toBe(2);
    expect($component->hasParameters())->toBeTrue();

    $paramOne = $component->parameters[0];
    expect($paramOne->name)->toBe(':messages');
    expect($paramOne->materializedName)->toBe('messages');
    expect($paramOne->value)->toBe('$messages');
    expect($paramOne->type)->toBe(ParameterType::DynamicVariable);

    $paramTwo = $component->parameters[1];
    expect($paramTwo->name)->toBe(':$userId');
    expect($paramTwo->materializedName)->toBe('user-id');
    expect($paramTwo->value)->toBe('$userId');

    $template = <<<'EOT'
<x-component 
        :messages="$messages":$userId/>
EOT;
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(1);
    expect($nodes[0])->toBeInstanceOf(ComponentNode::class);

    /** @var ComponentNode $component */
    $component = $nodes[0];

    expect($component->parameterCount)->toBe(2);
    expect($component->hasParameters())->toBeTrue();

    $paramOne = $component->parameters[0];
    expect($paramOne->name)->toBe(':messages');
    expect($paramOne->materializedName)->toBe('messages');
    expect($paramOne->value)->toBe('$messages');
    expect($paramOne->type)->toBe(ParameterType::DynamicVariable);

    $paramTwo = $component->parameters[1];
    expect($paramTwo->name)->toBe(':$userId');
    expect($paramTwo->materializedName)->toBe('user-id');
    expect($paramTwo->value)->toBe('$userId');

    $template = <<<'EOT'
<x-component 
        :messages="$messages"
        :$userId                        />
EOT;
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(1);
    expect($nodes[0])->toBeInstanceOf(ComponentNode::class);

    /** @var ComponentNode $component */
    $component = $nodes[0];

    expect($component->parameterCount)->toBe(2);
    expect($component->hasParameters())->toBeTrue();

    $paramOne = $component->parameters[0];
    expect($paramOne->name)->toBe(':messages');
    expect($paramOne->materializedName)->toBe('messages');
    expect($paramOne->value)->toBe('$messages');
    expect($paramOne->type)->toBe(ParameterType::DynamicVariable);

    $paramTwo = $component->parameters[1];
    expect($paramTwo->name)->toBe(':$userId');
    expect($paramTwo->materializedName)->toBe('user-id');
    expect($paramTwo->value)->toBe('$userId');
});

test('interpolated parameters are detected', function () {
    $template = <<<'EOT'
<x-button @click="update('{{ $name }}')" />
EOT;
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(1);
    expect($nodes[0])->toBeInstanceOf(ComponentNode::class);

    /** @var ComponentNode $component */
    $component = $nodes[0];
    expect($component->parameters)->toHaveCount(1);

    $paramOne = $component->parameters[0];

    expect($paramOne->name)->toBe('@click');
    expect($paramOne->type)->toBe(ParameterType::InterpolatedValue);
});

test('escaped parameters are detected', function () {
    $template = <<<'EOT'
<x-button ::class="{{ something }}" />
EOT;
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(1);
    expect($nodes[0])->toBeInstanceOf(ComponentNode::class);

    /** @var ComponentNode $component */
    $component = $nodes[0];
    expect($component->parameters)->toHaveCount(1);

    $paramOne = $component->parameters[0];

    expect($paramOne->name)->toBe('::class');
    expect($paramOne->materializedName)->toBe(':class');
    expect($paramOne->type)->toBe(ParameterType::EscapedParameter);
});

test('parser can be configured to only parse components', function () {
    $template = <<<'EOT'
<x-alert>
    {{ $title }} @if ($this) @endif
</x-alert>
EOT;
    $nodes = $this->parser()->onlyParseComponents()->parse($template);

    expect($nodes)->toHaveCount(3);
    expect($nodes[0])->toBeInstanceOf(ComponentNode::class);
    expect($nodes[1])->toBeInstanceOf(LiteralNode::class);
    expect($nodes[2])->toBeInstanceOf(ComponentNode::class);
});

test('echo params are parsed', function () {
    $template = <<<'EOT'
<x-alert
    data-one={{ $something }}
    data-two={{{ $something }}}
    data-three={!! $something !!}
/>
EOT;
    $nodes = $this->parser()->onlyParseComponents()->parse($template);

    expect($nodes)->toHaveCount(1);
    expect($nodes[0])->toBeInstanceOf(ComponentNode::class);

    /** @var ComponentNode $component */
    $component = $nodes[0];
    expect($component->parameters)->toHaveCount(3);

    /** @var ParameterNode $param1 */
    $param1 = $component->parameters[0];
    expect($param1->content)->toBe('data-one={{ $something }}');
    expect($param1->type)->toBe(ParameterType::UnknownEcho);

    /** @var ParameterNode $param2 */
    $param2 = $component->parameters[1];
    expect($param2->content)->toBe('data-two={{{ $something }}}');
    expect($param2->type)->toBe(ParameterType::UnknownTripleEcho);

    /** @var ParameterNode $param3 */
    $param3 = $component->parameters[2];
    expect($param3->content)->toBe('data-three={!! $something !!}');
    expect($param3->type)->toBe(ParameterType::UnknownRawEcho);
});
