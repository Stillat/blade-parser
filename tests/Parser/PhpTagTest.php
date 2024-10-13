<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Nodes\PhpTagNode;
use Stillat\BladeParser\Nodes\PhpTagType;

test('basic php tags', function () {
    $template = <<<'EOT'
<?php
    $variable = 'value';
?>
EOT;
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(1);
    expect($nodes[0])->toBeInstanceOf(PhpTagNode::class);

    /** @var PhpTagNode $phpTag */
    $phpTag = $nodes[0];
    expect($phpTag->type)->toBe(PhpTagType::PhpOpenTag);

    expect($phpTag->content)->toBe($template);
});

test('php tags neighboring literal nodes', function () {
    $template = <<<'EOT'
start<?php
    $variable = 'value';
?>end
EOT;
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(3);
    $this->assertLiteralContent($nodes[0], 'start');
    $this->assertLiteralContent($nodes[2], 'end');

    $phpContent = <<<'EOT'
<?php
    $variable = 'value';
?>
EOT;
    expect($nodes[1])->toBeInstanceOf(PhpTagNode::class);

    /** @var PhpTagNode $phpTag */
    $phpTag = $nodes[1];
    expect($phpTag->type)->toBe(PhpTagType::PhpOpenTag);
    expect($phpTag->content)->toBe($phpContent);
});

test('echo php tag', function () {
    $template = <<<'EOT'
start<?= $variable ?>end
EOT;
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(3);
    $this->assertLiteralContent($nodes[0], 'start');
    $this->assertLiteralContent($nodes[2], 'end');

    $phpContent = <<<'EOT'
<?= $variable ?>
EOT;

    expect($nodes[1])->toBeInstanceOf(PhpTagNode::class);

    /** @var PhpTagNode $phpTag */
    $phpTag = $nodes[1];
    expect($phpTag->type)->toBe(PhpTagType::PhpOpenTagWithEcho);
    expect($phpTag->content)->toBe($phpContent);
});

test('mixed php tag types', function () {
    $template = <<<'EOT'
start<?php $variable = 'value'; ?>inner<?= $variable ?>end
EOT;
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(5);
    $this->assertLiteralContent($nodes[0], 'start');
    $this->assertLiteralContent($nodes[2], 'inner');
    $this->assertLiteralContent($nodes[4], 'end');

    expect($nodes[1])->toBeInstanceOf(PhpTagNode::class);
    expect($nodes[3])->toBeInstanceOf(PhpTagNode::class);

    /** @var PhpTagNode $firstPhpNode */
    $firstPhpNode = $nodes[1];
    expect($firstPhpNode->type)->toBe(PhpTagType::PhpOpenTag);
    expect($firstPhpNode->content)->toBe('<?php $variable = \'value\'; ?>');

    /** @var PhpTagNode $secondPhpNode */
    $secondPhpNode = $nodes[3];
    expect($secondPhpNode->type)->toBe(PhpTagType::PhpOpenTagWithEcho);
    expect($secondPhpNode->content)->toBe('<?= $variable ?>');
});

test('php tags do not consume literal characters', function () {
    $template = <<<'EOT'
start <?php
 
 
?> end
EOT;
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(3);

    $this->assertLiteralContent($nodes[0], 'start ');
    $this->assertLiteralContent($nodes[2], ' end');

    expect($nodes[1])->toBeInstanceOf(PhpTagNode::class);

    $template = <<<'EOT'
start <?=
 
 
?> end
EOT;
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(3);

    $this->assertLiteralContent($nodes[0], 'start ');
    $this->assertLiteralContent($nodes[2], ' end');

    expect($nodes[1])->toBeInstanceOf(PhpTagNode::class);
});
