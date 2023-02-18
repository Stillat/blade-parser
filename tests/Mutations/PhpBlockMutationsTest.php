<?php

namespace Stillat\BladeParser\Tests\Mutations;

use Stillat\BladeParser\Tests\ParserTestCase;

class PhpBlockMutationsTest extends ParserTestCase
{
    public function testPhpBlockContentCanBeChanged()
    {
        $template = <<<'EOT'
One
    @php
    if ('this' == 'that') {
        doSomething();
    } else {
        doADifferentThing();
    }
    @endphp
Two
EOT;
        $doc = $this->getDocument($template);
        $phpBlock = $doc->getPhpBlocks()->first();
        $phpBlock->setContent('if (false != true) { exit; }');

        $expected = <<<'EXPECTED'
One
    @php 
    if (false != true) { exit; }
    @endphp
Two
EXPECTED;

        $this->assertSame($expected, (string) $doc);
    }

    public function testOriginalWhitespaceCanBeIgnored()
    {
        $template = <<<'EOT'
One
    @php
    
                                    $superIndented = true;
    
    @endphp
Two
EOT;
        $doc = $this->getDocument($template);
        $doc->getPhpBlocks()->first()->setContent('$cleanedWhitespace = true;', false);

        $expected = <<<'EXPECTED'
One
    @php $cleanedWhitespace = true; @endphp
Two
EXPECTED;

        $this->assertSame($expected, (string) $doc);
    }
}
