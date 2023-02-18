<?php

namespace Stillat\BladeParser\Tests\Mutations;

use Stillat\BladeParser\Nodes\Components\ParameterFactory;
use Stillat\BladeParser\Nodes\Components\ParameterNode;
use Stillat\BladeParser\Nodes\Components\ParameterType;
use Stillat\BladeParser\Tests\ParserTestCase;

class ComponentParametersTest extends ParserTestCase
{
    public function testCreatingParametersFromText()
    {
        $params = ParameterFactory::fromText('message="the message"');
        $this->assertCount(1, $params);

        /** @var ParameterNode $param */
        $param = $params[0];

        $this->assertSame('message="the message"', $param->content);
        $this->assertNotNull($param->nameNode);
        $this->assertSame('message', $param->nameNode->content);

        $this->assertNotNull($param->value);
        $this->assertSame('"the message"', $param->valueNode->content);
        $this->assertSame(ParameterType::Parameter, $param->type);

        $this->assertSame('message', $param->name);
        $this->assertSame('message', $param->materializedName);
        $this->assertSame('the message', $param->value);
    }

    public function testCreatingAttributesFromText()
    {
        $params = ParameterFactory::fromText('message');
        $this->assertCount(1, $params);

        /** @var ParameterNode $param */
        $param = $params[0];

        $this->assertSame('message', $param->content);
        $this->assertSame('', $param->value);
        $this->assertSame('message', $param->name);
        $this->assertSame('message', $param->materializedName);
        $this->assertNull($param->valueNode);
        $this->assertNotNull($param->nameNode);
        $this->assertSame('message', $param->nameNode->content);
        $this->assertSame(ParameterType::Attribute, $param->type);
    }

    public function testCreatingShortDynamicVariableFromText()
    {
        $params = ParameterFactory::fromText(':$message');
        $this->assertCount(1, $params);

        /** @var ParameterNode $param */
        $param = $params[0];

        $this->assertSame(':$message', $param->content);
        $this->assertNotNull($param->nameNode);
        $this->assertNull($param->valueNode);
        $this->assertSame(':$message', $param->nameNode->content);
        $this->assertSame(':$message', $param->name);
        $this->assertSame('message', $param->materializedName);
        $this->assertSame(ParameterType::ShorthandDynamicVariable, $param->type);
    }

    public function testCreatingInterpolatedValueFromText()
    {
        $params = ParameterFactory::fromText('message="the {{ $real }} message"');
        $this->assertCount(1, $params);

        /** @var ParameterNode $param */
        $param = $params[0];

        $this->assertSame('message="the {{ $real }} message"', $param->content);
        $this->assertNotNull($param->nameNode);
        $this->assertNotNull($param->valueNode);
        $this->assertSame(ParameterType::InterpolatedValue, $param->type);
        $this->assertSame('message', $param->name);
        $this->assertSame('message', $param->materializedName);
        $this->assertSame('the {{ $real }} message', $param->value);
        $this->assertSame('message', $param->nameNode->content);
        $this->assertSame('"the {{ $real }} message"', $param->valueNode->content);
    }

    public function testCreatingEscapedParameterFromText()
    {
        $param = ParameterFactory::parameterFromText('::message="this.message"');
        $this->assertNotNull($param);

        $this->assertSame('::message="this.message"', $param->content);
        $this->assertNotNull($param->nameNode);
        $this->assertNotNull($param->valueNode);
        $this->assertSame(ParameterType::EscapedParameter, $param->type);
        $this->assertSame('::message', $param->name);
        $this->assertSame(':message', $param->materializedName);
        $this->assertSame('this.message', $param->value);
        $this->assertSame('::message', $param->nameNode->content);
        $this->assertSame('"this.message"', $param->valueNode->content);
    }

    public function testCreatingAttributeEchoFromText()
    {
        $param = ParameterFactory::parameterFromText('{{ $attributes }}');
        $this->assertNotNull($param);
        $this->assertSame(ParameterType::AttributeEcho, $param->type);
        $this->assertSame('{{ $attributes }}', $param->content);
        $this->assertNull($param->nameNode);
        $this->assertNull($param->valueNode);
    }

    public function testUpdatingParameterValues()
    {
        $doc = $this->getDocument('One <x-alert message="a message" /> Two');
        $component = $doc->getComponents()->first();

        $param = $component->getParameter('message');
        $param->setValue('the new value');

        $this->assertTrue($param->isDirty());
        $this->assertTrue($component->isDirty());
        $this->assertSame('message="the new value"', $param->content);
        $this->assertNotNull($param->nameNode);
        $this->assertNotNull($param->valueNode);
        $this->assertSame('message', $param->nameNode->content);
        $this->assertSame('"the new value"', $param->valueNode->content);
        $this->assertSame(ParameterType::Parameter, $param->type);
        $this->assertSame('message', $param->name);
        $this->assertSame('message', $param->materializedName);
        $this->assertSame('the new value', $param->value);
    }

    public function testUpdatingParameterValuesWithQuotes()
    {
        $doc = $this->getDocument('One <x-alert message="a message" /> Two');
        $component = $doc->getComponents()->first();

        $param = $component->getParameter('message');
        $param->setValue('"the new value"');

        $this->assertTrue($param->isDirty());
        $this->assertTrue($component->isDirty());
        $this->assertSame('message="the new value"', $param->content);
        $this->assertNotNull($param->nameNode);
        $this->assertNotNull($param->valueNode);
        $this->assertSame('message', $param->nameNode->content);
        $this->assertSame('"the new value"', $param->valueNode->content);
        $this->assertSame(ParameterType::Parameter, $param->type);
        $this->assertSame('message', $param->name);
        $this->assertSame('message', $param->materializedName);
        $this->assertSame('the new value', $param->value);
    }

    public function testUpdatingParameterValuesWithSingleQuotes()
    {
        $doc = $this->getDocument("One <x-alert message='a message' /> Two");
        $component = $doc->getComponents()->first();

        $param = $component->getParameter('message');
        $param->setValue("'the new value'");

        $this->assertTrue($param->isDirty());
        $this->assertTrue($component->isDirty());
        $this->assertSame("message='the new value'", $param->content);
        $this->assertNotNull($param->nameNode);
        $this->assertNotNull($param->valueNode);
        $this->assertSame('message', $param->nameNode->content);
        $this->assertSame("'the new value'", $param->valueNode->content);
        $this->assertSame(ParameterType::Parameter, $param->type);
        $this->assertSame('message', $param->name);
        $this->assertSame('message', $param->materializedName);
        $this->assertSame('the new value', $param->value);
    }

    public function testUpdatingParameterName()
    {
        $doc = $this->getDocument("One <x-alert message='a message' /> Two");
        $component = $doc->getComponents()->first();

        $param = $component->getParameter('message');
        $param->setName('new_parameter_name');

        $this->assertTrue($param->isDirty());
        $this->assertTrue($component->isDirty());
        $this->assertSame("new_parameter_name='a message'", $param->content);
        $this->assertNotNull($param->nameNode);
        $this->assertNotNull($param->valueNode);
        $this->assertSame('new_parameter_name', $param->nameNode->content);
        $this->assertSame("'a message'", $param->valueNode->content);
        $this->assertSame(ParameterType::Parameter, $param->type);
        $this->assertSame('new_parameter_name', $param->name);
        $this->assertSame('new_parameter_name', $param->materializedName);
        $this->assertSame('a message', $param->value);
    }
}
