<?php

namespace Stillat\BladeParser\Tests\Mutations;

use Stillat\BladeParser\Errors\Exceptions\DuplicateParameterException;
use Stillat\BladeParser\Errors\Exceptions\InvalidParameterException;
use Stillat\BladeParser\Tests\ParserTestCase;

class ComponentMutationsTest extends ParserTestCase
{
    public function testDuplicateParameterInstancesThrowsException()
    {
        $this->expectException(DuplicateParameterException::class);
        $doc = $this->getDocument('One <x-alert message="a message" /> Two');
        $component = $doc->getComponents()->first();

        $param = $component->getParameter('message');
        $component->addParameter($param);
    }

    public function testComponentParametersCanBeRemoved()
    {
        $doc = $this->getDocument('One <x-alert message="a message" /> Two');
        $component = $doc->getComponents()->first();

        $param = $component->getParameter('message');
        $component->removeParameter($param);

        $this->assertSame(0, $component->parameterCount);
        $this->assertCount(0, $component->parameters);
        $this->assertFalse($component->hasParameter('message'));
    }

    public function testAddingAnInvalidParameterThrowsAnException()
    {
        $this->expectException(InvalidParameterException::class);

        $doc = $this->getDocument('One <x-alert message="a message" /> Two');
        $component = $doc->getComponents()->first();

        $component->addParameterFromText('');
    }

    public function testAddingAParameterFromText()
    {
        $doc = $this->getDocument('One <x-alert message="a message" /> Two');
        $component = $doc->getComponents()->first();

        $component->addParameterFromText('type="alert"');
        $this->assertCount(2, $component->parameters);
        $this->assertSame(2, $component->parameterCount);

        $this->assertSame('alert message="a message" type="alert" ', $component->innerContent);
        $this->assertSame(' message="a message" type="alert" ', $component->parameterContent);

        $this->assertSame('One <x-alert message="a message" type="alert" /> Two', (string) $doc);
    }

    public function testRenamingSelfClosingComponents()
    {
        $doc = $this->getDocument('One <x-alert message="a message" /> Two');
        $doc->getComponents()->first()->rename('new-name');
        $this->assertSame('One <x-new-name message="a message" /> Two', (string) $doc);
    }

    public function testRenamingColonSelfClosingComponents()
    {
        $doc = $this->getDocument('One <x:alert message="a message" /> Two');
        $doc->getComponents()->first()->rename('new-name');
        $this->assertSame('One <x-new-name message="a message" /> Two', (string) $doc);
    }

    public function testRenamingUnpairedComponent()
    {
        $doc = $this->getDocument('One <x-alert message="a message"> Two');
        $doc->getComponents()->first()->rename('new-name');
        $this->assertSame('One <x-new-name message="a message"> Two', (string) $doc);
    }

    public function testRenamingPairedComponent()
    {
        $doc = $this->getDocument('One <x-alert message="a message"> Two </x-alert> Three');
        $doc->resolveStructures();
        $doc->getComponents()->first()->rename('new-name');
        $this->assertSame('One <x-new-name message="a message"> Two </x-new-name> Three', (string) $doc);
    }

    public function testRenamingClosingComponentTagUpdatesOpeningTag()
    {
        $doc = $this->getDocument('One <x-alert message="a message"> Two </x-alert> Three');
        $doc->resolveStructures();
        $doc->getComponents()->last()->rename('new-name');
        $this->assertSame('One <x-new-name message="a message"> Two </x-new-name> Three', (string) $doc);
    }
}
