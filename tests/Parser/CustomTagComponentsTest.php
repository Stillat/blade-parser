<?php

namespace Stillat\BladeParser\Tests\Parser;

use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Tests\ParserTestCase;

class CustomTagComponentsTest extends ParserTestCase
{
    public function testCustomTagsCanBeParsed()
    {
        $template = <<<'EOT'
<x-alert :$message />
    <custom-alert :$message />
EOT;
        $parser = $this->parser();
        $parser->parse($template);

        $this->assertFalse($parser->hasCustomComponents());
        $this->assertTrue($parser->hasComponents());
        $this->assertTrue($parser->hasAnyComponents());

        $parser->registerCustomComponentTag('custom');
        $parser->parse($template);

        $this->assertTrue($parser->hasCustomComponents());
        $this->assertTrue($parser->hasComponents());
        $this->assertTrue($parser->hasAnyComponents());

        $doc = $this->getDocument($template, customComponentTags: ['custom']);

        /** @var ComponentNode[] $components */
        $components = $doc->findComponentsByTagName('alert');

        $this->assertCount(2, $components);

        $xAlert = $components[0];
        $this->assertFalse($xAlert->isCustomComponent);
        $this->assertSame('x', $xAlert->componentPrefix);
        $this->assertSame('x-alert', $xAlert->getCompareName());
        $this->assertSame('alert', $xAlert->getTagName());

        $customAlert = $components[1];
        $this->assertTrue($customAlert->isCustomComponent);
        $this->assertSame('custom', $customAlert->componentPrefix);
        $this->assertSame('custom-alert', $customAlert->getCompareName());
        $this->assertSame('alert', $customAlert->getTagName());
    }

    public function testCustomTagsDontGetConfusedWhenPairing()
    {
        $template = <<<'EOT'
<x-alert message="the message">
    <custom-alert message="something different">
    
    </custom-alert>
</x-alert>
EOT;
        $doc = $this->getDocument($template, customComponentTags: ['custom'])->resolveStructures();
        $this->assertCount(4, $doc->getComponents());

        $components = $doc->getComponents();

        $this->assertComponentsArePaired($components[0], $components[3]);
        $this->assertComponentsArePaired($components[1], $components[2]);
    }
}
