<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('basic component reflection', function () {
    $template = 'a<x-alert />b<x-alert />c<x-alert-two />';
    $document = $this->getDocument($template);

    expect($document->getLiterals())->toHaveCount(3);
    expect($document->findComponentsByTagName('alert'))->toHaveCount(2);
    expect($document->findComponentsByTagName('alert-two'))->toHaveCount(1);

    $nodes = $document->getNodes();

    // Should return the first one.
    expect($document->findComponentByTagName('alert'))->toBe($nodes[1]);
});

test('component has parameter', function () {
    $template = 'a<x-alert message="The message" />b<x-alert />c<x-alert-two />';
    $doc = $this->getDocument($template);
    expect($doc->hasAnyComponents())->toBeTrue();
    $component = $doc->getComponents()->first();

    $firstParam = $component->parameters[0];
    $firstParamByName = $component->getParameter('message');
    expect($firstParamByName)->not->toBeNull();
    expect($firstParam)->toEqual($firstParamByName);

    expect($component->hasParameterInstance($firstParam))->toBeTrue();
    expect($component->hasParameters())->toBeTrue();
    expect($component->hasParameter('some_parameter'))->toBeFalse();
    expect($component->hasParameter('message'))->toBeTrue();
});

test('component slot information', function () {
    $template = <<<'BLADE'
<x-input-with-slot>
    <x-slot:input class="text-input-lg" :name="'my_form_field'" data-test="data">Test</x-slot:input>
</x-input-with-slot>
BLADE;

    $slot = $this->getDocument($template)->findComponentByTagName('slot');

    expect($slot->isSlot())->toBeTrue();
    expect($slot->getTagName())->toBe('slot');
    expect($slot->getName())->toBe('input');

    $template = <<<'BLADE'
<x-input-with-slot>
    <x-slot :name="'my_form_field'" class="text-input-lg" :name="'my_form_field'" data-test="data">Test</x-slot>
</x-input-with-slot>
BLADE;

    $slot = $this->getDocument($template)->findComponentByTagName('slot');

    expect($slot->isSlot())->toBeTrue();
    expect($slot->getTagName())->toBe('slot');
    expect($slot->getName()->value)->toBe("'my_form_field'");
});
