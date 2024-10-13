<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Nodes\PhpTagNode;
use Stillat\BladeParser\Nodes\PhpTagType;

test('php tag content can be changed', function () {
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

    expect((string) $doc)->toBe($expected);
});

test('php tag types can be changed', function () {
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

    expect((string) $doc)->toBe($expected);
});

test('original whitespace can be overridden', function () {
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

    expect((string) $doc)->toBe($expected);
});

test('setting same type does not mark as dirty', function () {
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
    expect($phpTag->isDirty())->toBeFalse();
});
