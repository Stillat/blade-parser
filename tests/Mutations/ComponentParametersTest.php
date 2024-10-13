<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Nodes\Components\ParameterFactory;
use Stillat\BladeParser\Nodes\Components\ParameterNode;
use Stillat\BladeParser\Nodes\Components\ParameterType;

test('creating parameters from text', function () {
    $params = ParameterFactory::fromText('message="the message"');
    expect($params)->toHaveCount(1);

    /** @var ParameterNode $param */
    $param = $params[0];

    expect($param->content)->toBe('message="the message"');
    expect($param->nameNode)->not->toBeNull();
    expect($param->nameNode->content)->toBe('message');

    expect($param->value)->not->toBeNull();
    expect($param->valueNode->content)->toBe('"the message"');
    expect($param->type)->toBe(ParameterType::Parameter);

    expect($param->name)->toBe('message');
    expect($param->materializedName)->toBe('message');
    expect($param->value)->toBe('the message');
});

test('creating attributes from text', function () {
    $params = ParameterFactory::fromText('message');
    expect($params)->toHaveCount(1);

    /** @var ParameterNode $param */
    $param = $params[0];

    expect($param->content)->toBe('message');
    expect($param->value)->toBe('');
    expect($param->name)->toBe('message');
    expect($param->materializedName)->toBe('message');
    expect($param->valueNode)->toBeNull();
    expect($param->nameNode)->not->toBeNull();
    expect($param->nameNode->content)->toBe('message');
    expect($param->type)->toBe(ParameterType::Attribute);
});

test('creating short dynamic variable from text', function () {
    $params = ParameterFactory::fromText(':$message');
    expect($params)->toHaveCount(1);

    /** @var ParameterNode $param */
    $param = $params[0];

    expect($param->content)->toBe(':$message');
    expect($param->nameNode)->not->toBeNull();
    expect($param->valueNode)->toBeNull();
    expect($param->nameNode->content)->toBe(':$message');
    expect($param->name)->toBe(':$message');
    expect($param->materializedName)->toBe('message');
    expect($param->type)->toBe(ParameterType::ShorthandDynamicVariable);
});

test('creating interpolated value from text', function () {
    $params = ParameterFactory::fromText('message="the {{ $real }} message"');
    expect($params)->toHaveCount(1);

    /** @var ParameterNode $param */
    $param = $params[0];

    expect($param->content)->toBe('message="the {{ $real }} message"');
    expect($param->nameNode)->not->toBeNull();
    expect($param->valueNode)->not->toBeNull();
    expect($param->type)->toBe(ParameterType::InterpolatedValue);
    expect($param->name)->toBe('message');
    expect($param->materializedName)->toBe('message');
    expect($param->value)->toBe('the {{ $real }} message');
    expect($param->nameNode->content)->toBe('message');
    expect($param->valueNode->content)->toBe('"the {{ $real }} message"');
});

test('creating escaped parameter from text', function () {
    $param = ParameterFactory::parameterFromText('::message="this.message"');
    expect($param)->not->toBeNull();

    expect($param->content)->toBe('::message="this.message"');
    expect($param->nameNode)->not->toBeNull();
    expect($param->valueNode)->not->toBeNull();
    expect($param->type)->toBe(ParameterType::EscapedParameter);
    expect($param->name)->toBe('::message');
    expect($param->materializedName)->toBe(':message');
    expect($param->value)->toBe('this.message');
    expect($param->nameNode->content)->toBe('::message');
    expect($param->valueNode->content)->toBe('"this.message"');
});

test('creating attribute echo from text', function () {
    $param = ParameterFactory::parameterFromText('{{ $attributes }}');
    expect($param)->not->toBeNull();
    expect($param->type)->toBe(ParameterType::AttributeEcho);
    expect($param->content)->toBe('{{ $attributes }}');
    expect($param->nameNode)->toBeNull();
    expect($param->valueNode)->toBeNull();
});

test('updating parameter values', function () {
    $doc = $this->getDocument('One <x-alert message="a message" /> Two');
    $component = $doc->getComponents()->first();

    $param = $component->getParameter('message');
    $param->setValue('the new value');

    expect($param->isDirty())->toBeTrue();
    expect($component->isDirty())->toBeTrue();
    expect($param->content)->toBe('message="the new value"');
    expect($param->nameNode)->not->toBeNull();
    expect($param->valueNode)->not->toBeNull();
    expect($param->nameNode->content)->toBe('message');
    expect($param->valueNode->content)->toBe('"the new value"');
    expect($param->type)->toBe(ParameterType::Parameter);
    expect($param->name)->toBe('message');
    expect($param->materializedName)->toBe('message');
    expect($param->value)->toBe('the new value');
});

test('updating parameter values with quotes', function () {
    $doc = $this->getDocument('One <x-alert message="a message" /> Two');
    $component = $doc->getComponents()->first();

    $param = $component->getParameter('message');
    $param->setValue('"the new value"');

    expect($param->isDirty())->toBeTrue();
    expect($component->isDirty())->toBeTrue();
    expect($param->content)->toBe('message="the new value"');
    expect($param->nameNode)->not->toBeNull();
    expect($param->valueNode)->not->toBeNull();
    expect($param->nameNode->content)->toBe('message');
    expect($param->valueNode->content)->toBe('"the new value"');
    expect($param->type)->toBe(ParameterType::Parameter);
    expect($param->name)->toBe('message');
    expect($param->materializedName)->toBe('message');
    expect($param->value)->toBe('the new value');
});

test('updating parameter values with single quotes', function () {
    $doc = $this->getDocument("One <x-alert message='a message' /> Two");
    $component = $doc->getComponents()->first();

    $param = $component->getParameter('message');
    $param->setValue("'the new value'");

    expect($param->isDirty())->toBeTrue();
    expect($component->isDirty())->toBeTrue();
    expect($param->content)->toBe("message='the new value'");
    expect($param->nameNode)->not->toBeNull();
    expect($param->valueNode)->not->toBeNull();
    expect($param->nameNode->content)->toBe('message');
    expect($param->valueNode->content)->toBe("'the new value'");
    expect($param->type)->toBe(ParameterType::Parameter);
    expect($param->name)->toBe('message');
    expect($param->materializedName)->toBe('message');
    expect($param->value)->toBe('the new value');
});

test('updating parameter name', function () {
    $doc = $this->getDocument("One <x-alert message='a message' /> Two");
    $component = $doc->getComponents()->first();

    $param = $component->getParameter('message');
    $param->setName('new_parameter_name');

    expect($param->isDirty())->toBeTrue();
    expect($component->isDirty())->toBeTrue();
    expect($param->content)->toBe("new_parameter_name='a message'");
    expect($param->nameNode)->not->toBeNull();
    expect($param->valueNode)->not->toBeNull();
    expect($param->nameNode->content)->toBe('new_parameter_name');
    expect($param->valueNode->content)->toBe("'a message'");
    expect($param->type)->toBe(ParameterType::Parameter);
    expect($param->name)->toBe('new_parameter_name');
    expect($param->materializedName)->toBe('new_parameter_name');
    expect($param->value)->toBe('a message');
});
