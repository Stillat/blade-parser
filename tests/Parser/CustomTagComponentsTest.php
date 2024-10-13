<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Nodes\Components\ComponentNode;

test('custom tags can be parsed', function () {
    $template = <<<'EOT'
<x-alert :$message />
    <custom-alert :$message />
EOT;
    $parser = $this->parser();
    $parser->parse($template);

    expect($parser->hasCustomComponents())->toBeFalse();
    expect($parser->hasComponents())->toBeTrue();
    expect($parser->hasAnyComponents())->toBeTrue();

    $parser->registerCustomComponentTag('custom');
    $parser->parse($template);

    expect($parser->hasCustomComponents())->toBeTrue();
    expect($parser->hasComponents())->toBeTrue();
    expect($parser->hasAnyComponents())->toBeTrue();

    $doc = $this->getDocument($template, customComponentTags: ['custom']);

    /** @var ComponentNode[] $components */
    $components = $doc->findComponentsByTagName('alert');

    expect($components)->toHaveCount(2);

    $xAlert = $components[0];
    expect($xAlert->isCustomComponent)->toBeFalse();
    expect($xAlert->componentPrefix)->toBe('x');
    expect($xAlert->getCompareName())->toBe('x-alert');
    expect($xAlert->getTagName())->toBe('alert');

    $customAlert = $components[1];
    expect($customAlert->isCustomComponent)->toBeTrue();
    expect($customAlert->componentPrefix)->toBe('custom');
    expect($customAlert->getCompareName())->toBe('custom-alert');
    expect($customAlert->getTagName())->toBe('alert');
});

test('custom tags dont get confused when pairing', function () {
    $template = <<<'EOT'
<x-alert message="the message">
    <custom-alert message="something different">
    
    </custom-alert>
</x-alert>
EOT;
    $doc = $this->getDocument($template, customComponentTags: ['custom'])->resolveStructures();
    expect($doc->getComponents())->toHaveCount(4);

    $components = $doc->getComponents();

    $this->assertComponentsArePaired($components[0], $components[3]);
    $this->assertComponentsArePaired($components[1], $components[2]);
});
