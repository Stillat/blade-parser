<?php

namespace Stillat\BladeParser\Tests\Parser;

use Stillat\BladeParser\Nodes\PhpTagNode;
use Stillat\BladeParser\Nodes\PhpTagType;
use Stillat\BladeParser\Tests\ParserTestCase;

class PhpTagTest extends ParserTestCase
{
    public function testBasicPhpTags()
    {
        $template = <<<'EOT'
<?php
    $variable = 'value';
?>
EOT;
        $nodes = $this->parseNodes($template);
        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(PhpTagNode::class, $nodes[0]);

        /** @var PhpTagNode $phpTag */
        $phpTag = $nodes[0];
        $this->assertSame(PhpTagType::PhpOpenTag, $phpTag->type);

        $this->assertSame($template, $phpTag->content);
    }

    public function testPhpTagsNeighboringLiteralNodes()
    {
        $template = <<<'EOT'
start<?php
    $variable = 'value';
?>end
EOT;
        $nodes = $this->parseNodes($template);
        $this->assertCount(3, $nodes);
        $this->assertLiteralContent($nodes[0], 'start');
        $this->assertLiteralContent($nodes[2], 'end');

        $phpContent = <<<'EOT'
<?php
    $variable = 'value';
?>
EOT;
        $this->assertInstanceOf(PhpTagNode::class, $nodes[1]);

        /** @var PhpTagNode $phpTag */
        $phpTag = $nodes[1];
        $this->assertSame(PhpTagType::PhpOpenTag, $phpTag->type);
        $this->assertSame($phpContent, $phpTag->content);
    }

    public function testEchoPhpTag()
    {
        $template = <<<'EOT'
start<?= $variable ?>end
EOT;
        $nodes = $this->parseNodes($template);
        $this->assertCount(3, $nodes);
        $this->assertLiteralContent($nodes[0], 'start');
        $this->assertLiteralContent($nodes[2], 'end');

        $phpContent = <<<'EOT'
<?= $variable ?>
EOT;

        $this->assertInstanceOf(PhpTagNode::class, $nodes[1]);

        /** @var PhpTagNode $phpTag */
        $phpTag = $nodes[1];
        $this->assertSame(PhpTagType::PhpOpenTagWithEcho, $phpTag->type);
        $this->assertSame($phpContent, $phpTag->content);
    }

    public function testMixedPhpTagTypes()
    {
        $template = <<<'EOT'
start<?php $variable = 'value'; ?>inner<?= $variable ?>end
EOT;
        $nodes = $this->parseNodes($template);
        $this->assertCount(5, $nodes);
        $this->assertLiteralContent($nodes[0], 'start');
        $this->assertLiteralContent($nodes[2], 'inner');
        $this->assertLiteralContent($nodes[4], 'end');

        $this->assertInstanceOf(PhpTagNode::class, $nodes[1]);
        $this->assertInstanceOf(PhpTagNode::class, $nodes[3]);

        /** @var PhpTagNode $firstPhpNode */
        $firstPhpNode = $nodes[1];
        $this->assertSame(PhpTagType::PhpOpenTag, $firstPhpNode->type);
        $this->assertSame('<?php $variable = \'value\'; ?>', $firstPhpNode->content);

        /** @var PhpTagNode $secondPhpNode */
        $secondPhpNode = $nodes[3];
        $this->assertSame(PhpTagType::PhpOpenTagWithEcho, $secondPhpNode->type);
        $this->assertSame('<?= $variable ?>', $secondPhpNode->content);
    }

    public function testPhpTagsDoNotConsumeLiteralCharacters()
    {
        $template = <<<'EOT'
start <?php
 
 
?> end
EOT;
        $nodes = $this->parseNodes($template);
        $this->assertCount(3, $nodes);

        $this->assertLiteralContent($nodes[0], 'start ');
        $this->assertLiteralContent($nodes[2], ' end');

        $this->assertInstanceOf(PhpTagNode::class, $nodes[1]);

        $template = <<<'EOT'
start <?=
 
 
?> end
EOT;
        $nodes = $this->parseNodes($template);
        $this->assertCount(3, $nodes);

        $this->assertLiteralContent($nodes[0], 'start ');
        $this->assertLiteralContent($nodes[2], ' end');

        $this->assertInstanceOf(PhpTagNode::class, $nodes[1]);
    }
}
