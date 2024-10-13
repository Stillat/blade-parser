<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Nodes\Components\ComponentNode;

test('basic component pairing', function () {
    $template = <<<'EOT'
<div>
    <x-alert message="the message">
        <p>Inner text.</p>
    </x-alert>
</div>
EOT;
    $doc = $this->getDocument($template);
    $doc->resolveStructures();

    $components = $doc->getComponents();
    $c1 = $components[0];
    $c2 = $components[1];

    $this->assertComponentsArePaired($c1, $c2);
});

test('self closing components are skipped', function () {
    $template = <<<'EOT'
<div>
    <x-alert message="the message">
        <p>Inner text.</p>
        <x-alert message="a different message" />
    </x-alert>
</div>
EOT;
    $doc = $this->getDocument($template);
    $doc->resolveStructures();

    $components = $doc->getComponents();
    $c1 = $components[0];

    /** @var ComponentNode $c2 */
    $c2 = $components[1];
    $c3 = $components[2];

    expect($c2->isClosedBy)->toBeNull();
    $this->assertComponentsArePaired($c1, $c3);
});

test('deeply nested components are paired', function () {
    $numberOfTags = 50;

    $template = str_repeat('<x-alert message="a message">', $numberOfTags);
    $template .= str_repeat('</x-alert>', $numberOfTags);
    $doc = $this->getDocument($template);
    $doc->resolveStructures();
    $components = $doc->getComponents()->values()->all();
    expect($components)->toHaveCount(100);

    assertManyComponentsArePaired($components);
});

test('deeply nested mixed components are paired', function () {
    $numberOfTags = 50;

    $template = str_repeat('<x-alert message="a message">', $numberOfTags);
    $template .= str_repeat('<x-banner type="some type">', $numberOfTags);
    $template .= str_repeat('</x-banner>', $numberOfTags);
    $template .= str_repeat('</x-alert>', $numberOfTags);

    $doc = $this->getDocument($template);
    $doc->resolveStructures();
    $components = $doc->getComponents()->values()->all();
    expect($components)->toHaveCount(200);

    assertManyComponentsArePaired($components);
});

test('namespaced component pairing', function () {
    $template = <<<'EOT'
One <x:third> Two </x:third> Three
EOT;
    $doc = $this->getDocument($template);
    $doc->resolveStructures();

    /** @var ComponentNode[] $components */
    $components = $doc->getComponents()->values()->all();

    expect($components)->toHaveCount(2);

    $c1 = $components[0];
    $c2 = $components[1];

    $this->assertComponentsArePaired($c1, $c2);
});

test('mixed style component pairing', function () {
    $template = <<<'EOT'
One <x:third> Two </x-third> Three
EOT;
    $doc = $this->getDocument($template);
    $doc->resolveStructures();

    /** @var ComponentNode[] $components */
    $components = $doc->getComponents()->values()->all();

    expect($components)->toHaveCount(2);

    $c1 = $components[0];
    $c2 = $components[1];

    $this->assertComponentsArePaired($c1, $c2);
});

test('mixed style component pairing two', function () {
    $template = <<<'EOT'
One <x-third> Two </x:third> Three
EOT;
    $doc = $this->getDocument($template);
    $doc->resolveStructures();

    /** @var ComponentNode[] $components */
    $components = $doc->getComponents()->values()->all();

    expect($components)->toHaveCount(2);

    $c1 = $components[0];
    $c2 = $components[1];

    $this->assertComponentsArePaired($c1, $c2);
});

test('mixed style component pairing three', function () {
    $template = <<<'EOT'
One <x-slot:slot_name> Two </x:slot> Three
EOT;
    $doc = $this->getDocument($template);
    $doc->resolveStructures();

    /** @var ComponentNode[] $components */
    $components = $doc->getComponents()->values()->all();

    expect($components)->toHaveCount(2);

    $c1 = $components[0];
    $c2 = $components[1];

    $this->assertComponentsArePaired($c1, $c2);
});

test('mixed style component pairing four', function () {
    $template = <<<'EOT'
One <x-slot:slot_name> <x-slot:name> </x-slot:name> </x:slot> Three
EOT;
    $doc = $this->getDocument($template);
    $doc->resolveStructures();

    /** @var ComponentNode[] $components */
    $components = $doc->getComponents()->values()->all();

    expect($components)->toHaveCount(4);

    $c1 = $components[0];
    $c2 = $components[1];
    $c3 = $components[2];
    $c4 = $components[3];

    $this->assertComponentsArePaired($c1, $c4);
    $this->assertComponentsArePaired($c2, $c3);
});
