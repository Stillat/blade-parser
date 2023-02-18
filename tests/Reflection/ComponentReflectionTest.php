<?php

namespace Stillat\BladeParser\Tests\Reflection;

use Stillat\BladeParser\Tests\ParserTestCase;

class ComponentReflectionTest extends ParserTestCase
{
    public function testBasicComponentReflection()
    {
        $template = 'a<x-alert />b<x-alert />c<x-alert-two />';
        $document = $this->getDocument($template);

        $this->assertCount(3, $document->getLiterals());
        $this->assertCount(2, $document->findComponentsByTagName('alert'));
        $this->assertCount(1, $document->findComponentsByTagName('alert-two'));

        $nodes = $document->getNodes();
        // Should return the first one.
        $this->assertSame($nodes[1], $document->findComponentByTagName('alert'));
    }

    public function testComponentHasParameter()
    {
        $template = 'a<x-alert message="The message" />b<x-alert />c<x-alert-two />';
        $doc = $this->getDocument($template);
        $this->assertTrue($doc->hasAnyComponents());
        $component = $doc->getComponents()->first();

        $firstParam = $component->parameters[0];
        $firstParamByName = $component->getParameter('message');
        $this->assertNotNull($firstParamByName);
        $this->assertEquals($firstParamByName, $firstParam);

        $this->assertTrue($component->hasParameterInstance($firstParam));
        $this->assertTrue($component->hasParameters());
        $this->assertFalse($component->hasParameter('some_parameter'));
        $this->assertTrue($component->hasParameter('message'));
    }
}
