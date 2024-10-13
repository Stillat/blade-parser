<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('php block content can be changed', function () {
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

    expect((string) $doc)->toBe($expected);
});

test('original whitespace can be ignored', function () {
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

    expect((string) $doc)->toBe($expected);
});
