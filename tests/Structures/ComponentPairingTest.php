<?php

namespace Stillat\BladeParser\Tests\Structures;

use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Tests\ParserTestCase;

class ComponentPairingTest extends ParserTestCase
{
    public function testBasicComponentPairing()
    {
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
    }

    public function testSelfClosingComponentsAreSkipped()
    {
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

        $this->assertNull($c2->isClosedBy);
        $this->assertComponentsArePaired($c1, $c3);
    }

    public function testDeeplyNestedComponentsArePaired()
    {
        $numberOfTags = 50;

        $template = str_repeat('<x-alert message="a message">', $numberOfTags);
        $template .= str_repeat('</x-alert>', $numberOfTags);
        $doc = $this->getDocument($template);
        $doc->resolveStructures();
        $components = $doc->getComponents()->values()->all();
        $this->assertCount(100, $components);

        $this->assertManyComponentsArePaired($components);
    }

    public function testDeeplyNestedMixedComponentsArePaired()
    {
        $numberOfTags = 50;

        $template = str_repeat('<x-alert message="a message">', $numberOfTags);
        $template .= str_repeat('<x-banner type="some type">', $numberOfTags);
        $template .= str_repeat('</x-banner>', $numberOfTags);
        $template .= str_repeat('</x-alert>', $numberOfTags);

        $doc = $this->getDocument($template);
        $doc->resolveStructures();
        $components = $doc->getComponents()->values()->all();
        $this->assertCount(200, $components);

        $this->assertManyComponentsArePaired($components);
    }

    public function testNamespacedComponentPairing()
    {
        $template = <<<'EOT'
One <x:third> Two </x:third> Three
EOT;
        $doc = $this->getDocument($template);
        $doc->resolveStructures();

        /** @var ComponentNode[] $components */
        $components = $doc->getComponents()->values()->all();

        $this->assertCount(2, $components);

        $c1 = $components[0];
        $c2 = $components[1];

        $this->assertComponentsArePaired($c1, $c2);
    }

    public function testMixedStyleComponentPairing()
    {
        $template = <<<'EOT'
One <x:third> Two </x-third> Three
EOT;
        $doc = $this->getDocument($template);
        $doc->resolveStructures();

        /** @var ComponentNode[] $components */
        $components = $doc->getComponents()->values()->all();

        $this->assertCount(2, $components);

        $c1 = $components[0];
        $c2 = $components[1];

        $this->assertComponentsArePaired($c1, $c2);
    }

    public function testMixedStyleComponentPairingTwo()
    {
        $template = <<<'EOT'
One <x-third> Two </x:third> Three
EOT;
        $doc = $this->getDocument($template);
        $doc->resolveStructures();

        /** @var ComponentNode[] $components */
        $components = $doc->getComponents()->values()->all();

        $this->assertCount(2, $components);

        $c1 = $components[0];
        $c2 = $components[1];

        $this->assertComponentsArePaired($c1, $c2);
    }

    public function testMixedStyleComponentPairingThree()
    {
        $template = <<<'EOT'
One <x-slot:slot_name> Two </x:slot> Three
EOT;
        $doc = $this->getDocument($template);
        $doc->resolveStructures();

        /** @var ComponentNode[] $components */
        $components = $doc->getComponents()->values()->all();

        $this->assertCount(2, $components);

        $c1 = $components[0];
        $c2 = $components[1];

        $this->assertComponentsArePaired($c1, $c2);
    }

    public function testMixedStyleComponentPairingFour()
    {
        $template = <<<'EOT'
One <x-slot:slot_name> <x-slot:name> </x-slot:name> </x:slot> Three
EOT;
        $doc = $this->getDocument($template);
        $doc->resolveStructures();

        /** @var ComponentNode[] $components */
        $components = $doc->getComponents()->values()->all();

        $this->assertCount(4, $components);

        $c1 = $components[0];
        $c2 = $components[1];
        $c3 = $components[2];
        $c4 = $components[3];

        $this->assertComponentsArePaired($c1, $c4);
        $this->assertComponentsArePaired($c2, $c3);
    }

    /**
     * @param  ComponentNode[]  $components
     */
    private function assertManyComponentsArePaired(array $components): void
    {
        $componentCount = count($components);
        $limit = $componentCount / 2;

        for ($i = 0; $i < $limit; $i++) {
            $closeIndex = $componentCount - ($i + 1);
            $openComponent = $components[$i];
            $closeComponent = $components[$closeIndex];

            $this->assertComponentsArePaired($openComponent, $closeComponent);
        }
    }
}
