<?php

namespace Stillat\BladeParser\Tests\Parser;

use Stillat\BladeParser\Nodes\PhpBlockNode;
use Stillat\BladeParser\Tests\ParserTestCase;

class PhpBlockTest extends ParserTestCase
{
    public function testPhpBlockDoesNotConsumeLiteralCharacter()
    {
        $template = <<<'EOT'
start @php
 
 
@endphp end
EOT;
        $nodes = $this->parseNodes($template);
        $this->assertCount(3, $nodes);

        $this->assertLiteralContent($nodes[0], 'start ');
        $this->assertLiteralContent($nodes[2], ' end');

        $this->assertInstanceOf(PhpBlockNode::class, $nodes[1]);
    }

    public function testManyOpeningPhpBlockDirectives()
    {
        $template = <<<'EOT'
@php @php @php $counter++;
@endphp
EOT;
        $expected = <<<'EXP'
<?php @php @php $counter++; ?>
EXP;

        $this->assertSame($expected, $this->compiler->compileString($template));
    }

    public function testNeighboringPhpBlockDirectives()
    {
        $template = <<<'EOT'
@php
    $counter += 1;
@endphp @php
    $counter += 2;
@endphp
EOT;
        $expected = <<<'EXP'
<?php $counter += 1; ?> <?php $counter += 2; ?>
EXP;

        $this->assertSame($expected, $this->compiler->compileString($template));
    }

    public function testDetachedPhpBlockDirectivesWithValidPhpBlocks()
    {
        $template = <<<'EOT'
@php @php
$counter += 1;
@endphp @php
$counter += 2;
@endphp @php @php @php @php $counter += 3; @endphp
EOT;
        $expected = <<<'EXP'
<?php @php
$counter += 1; ?> <?php $counter += 2; ?> <?php @php @php @php $counter += 3; ?>
EXP;

        $this->assertSame($expected, $this->compiler->compileString($template));
    }

    public function testPhpBlocksContainingLoops()
    {
        $template = <<<'EOT'
@php $counter++;
for($i = 0; $i++;$=) {}
@endphp @php $counter_two++;
for($i = 0; $i++;$=two) {}
@endphp
EOT;
        $expected = <<<'EXP'
<?php $counter++;
for($i = 0; $i++;$=) {} ?> <?php $counter_two++;
for($i = 0; $i++;$=two) {} ?>
EXP;
        $this->assertSame($expected, $this->compiler->compileString($template));
    }
}
