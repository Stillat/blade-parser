<?php

namespace Stillat\BladeParser\Tests\Mutations;

use Stillat\BladeParser\Nodes\PhpTagNode;
use Stillat\BladeParser\Nodes\PhpTagType;
use Stillat\BladeParser\Tests\ParserTestCase;

class PhpTagMutationsTest extends ParserTestCase
{
    public function testPhpTagContentCanBeChanged()
    {
        $template = <<<'EOT'
One
    <?php
            $embeddedPhp = true;
                ?>
Two
EOT;
        $doc = $this->getDocument($template);
        $doc->getPhpTags()->first()->setContent('$hello = "world";');

        $expected = <<<'EXPECTED'
One
    <?php 
            $hello = "world";
                ?>
Two
EXPECTED;

        $this->assertSame($expected, (string) $doc);
    }

    public function testPhpTagTypesCanBeChanged()
    {
        $template = <<<'EOT'
One
    <?php
            $embeddedPhp;
                ?>
Two
EOT;
        $doc = $this->getDocument($template);
        $doc->getPhpTags()->first()->setType(PhpTagType::PhpOpenTagWithEcho);

        $expected = <<<'EXPECTED'
One
    <?= 
            $embeddedPhp;
                ?>
Two
EXPECTED;

        $this->assertSame($expected, (string) $doc);
    }

    public function testOriginalWhitespaceCanBeOverridden()
    {
        $template = <<<'EOT'
One
    <?php
            $embeddedPhp = true;
                ?>
Two
EOT;
        $doc = $this->getDocument($template);
        $doc->getPhpTags()->first()->setContent('$hello = "world";', false);

        $expected = <<<'EXPECTED'
One
    <?php $hello = "world"; ?>
Two
EXPECTED;

        $this->assertSame($expected, (string) $doc);
    }

    public function testSettingSameTypeDoesNotMarkAsDirty()
    {
        $template = <<<'EOT'
One
    <?php
            $embeddedPhp = true;
                ?>
Two
EOT;
        $doc = $this->getDocument($template);
        /** @var PhpTagNode $phpTag */
        $phpTag = $doc->getPhpTags()->first();

        $phpTag->setType($phpTag->type);
        $this->assertFalse($phpTag->isDirty());
    }
}
