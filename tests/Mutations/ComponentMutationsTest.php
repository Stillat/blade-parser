<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Errors\Exceptions\DuplicateParameterException;
use Stillat\BladeParser\Errors\Exceptions\InvalidParameterException;

test('duplicate parameter instances throws exception', function () {
    $this->expectException(DuplicateParameterException::class);
    $doc = $this->getDocument('One <x-alert message="a message" /> Two');
    $component = $doc->getComponents()->first();

    $param = $component->getParameter('message');
    $component->addParameter($param);
});

test('component parameters can be removed', function () {
    $doc = $this->getDocument('One <x-alert message="a message" /> Two');
    $component = $doc->getComponents()->first();

    $param = $component->getParameter('message');
    $component->removeParameter($param);

    expect($component->parameterCount)->toBe(0);
    expect($component->parameters)->toHaveCount(0);
    expect($component->hasParameter('message'))->toBeFalse();
});

test('adding an invalid parameter throws an exception', function () {
    $this->expectException(InvalidParameterException::class);

    $doc = $this->getDocument('One <x-alert message="a message" /> Two');
    $component = $doc->getComponents()->first();

    $component->addParameterFromText('');
});

test('adding aparameter from text', function () {
    $doc = $this->getDocument('One <x-alert message="a message" /> Two');
    $component = $doc->getComponents()->first();

    $component->addParameterFromText('type="alert"');
    expect($component->parameters)->toHaveCount(2);
    expect($component->parameterCount)->toBe(2);

    expect($component->innerContent)->toBe('alert message="a message" type="alert" ');
    expect($component->parameterContent)->toBe(' message="a message" type="alert" ');

    expect((string) $doc)->toBe('One <x-alert message="a message" type="alert" /> Two');
});

test('renaming self closing components', function () {
    $doc = $this->getDocument('One <x-alert message="a message" /> Two');
    $doc->getComponents()->first()->rename('new-name');
    expect((string) $doc)->toBe('One <x-new-name message="a message" /> Two');
});

test('renaming colon self closing components', function () {
    $doc = $this->getDocument('One <x:alert message="a message" /> Two');
    $doc->getComponents()->first()->rename('new-name');
    expect((string) $doc)->toBe('One <x-new-name message="a message" /> Two');
});

test('renaming unpaired component', function () {
    $doc = $this->getDocument('One <x-alert message="a message"> Two');
    $doc->getComponents()->first()->rename('new-name');
    expect((string) $doc)->toBe('One <x-new-name message="a message"> Two');
});

test('renaming paired component', function () {
    $doc = $this->getDocument('One <x-alert message="a message"> Two </x-alert> Three');
    $doc->resolveStructures();
    $doc->getComponents()->first()->rename('new-name');
    expect((string) $doc)->toBe('One <x-new-name message="a message"> Two </x-new-name> Three');
});

test('renaming closing component tag updates opening tag', function () {
    $doc = $this->getDocument('One <x-alert message="a message"> Two </x-alert> Three');
    $doc->resolveStructures();
    $doc->getComponents()->last()->rename('new-name');
    expect((string) $doc)->toBe('One <x-new-name message="a message"> Two </x-new-name> Three');
});
