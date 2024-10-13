<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Nodes\AbstractNode;

test('file path can be set', function () {
    $document = Document::fromText('Simple template', 'resources/views/layout.validation.php');
    expect($document->getFilePath())->toBe('resources/views/layout.validation.php');
});

test('document to document does not return same instance', function () {
    $input = <<<'EOT'
@if ($this)
    @else ($that)
        @endif
EOT;
    $doc = $this->getDocument($input);
    $doc2 = $doc->toDocument();
    expect($doc2)->not->toBeNull();
    $this->assertNotSame($doc, $doc2);

    expect((string) $doc)->toBe($input);
    expect((string) $doc2)->toBe((string) $doc);
});

test('document text extraction', function () {
    $input = <<<'EOT'
A@@include ('one')B
    C@include ('two')D
        E@@include ('three')F
EOT;
    $doc = $this->getDocument($input);

    $expected = <<<'EXPECTED'
A@include ('one')B
    CD
        E@include ('three')F
EXPECTED;
    expect($doc->extractText())->toBe($expected);

    $expected = <<<'EXPECTED'
A@@include ('one')B
    CD
        E@@include ('three')F
EXPECTED;
    expect($doc->extractText(false))->toBe($expected);
});

test('documents release nodes', function () {
    $input = <<<'EOT'
@if ($this)
    @else ($that)
        @endif
EOT;
    $doc = $this->getDocument($input);
    $nodes = $doc->getNodes();

    /** @var AbstractNode $node */
    foreach ($nodes as $node) {
        expect($node->hasDocument())->toBeTrue();
        expect($node->getDocument())->toBe($doc);
    }

    $doc->releaseNodesFromDocument();

    /** @var AbstractNode $node */
    foreach ($nodes as $node) {
        expect($node->hasDocument())->toBeFalse();
        expect($node->getDocument())->toBeNull();
    }
});

test('document line count', function () {
    expect($this->getDocument('')->getLineCount())->toBe(1);
    expect($this->getDocument(str_repeat("\n", 3))->getLineCount())->toBe(3);
});

test('line number from offset', function () {
    $input = <<<'EOT'
@if ('this')
    then
@endif
EOT;
    $doc = $this->getDocument($input);

    $line = 1;
    for ($i = 0; $i < strlen($input); $i++) {
        expect($doc->getLineNumberFromOffset($i))->toBe($line);
        if ($input[$i] == "\n") {
            $line += 1;
        }
    }
});

test('column number from offset', function () {
    $input = <<<'EOT'
@if ('this')
    then
@endif
EOT;
    $doc = $this->getDocument($input);

    $column = 1;
    for ($i = 0; $i < strlen($input); $i++) {
        expect($doc->getColumnNumberFromOffset($i))->toBe($column);
        if ($input[$i] == "\n") {
            $column = 1;
        } else {
            $column += 1;
        }
    }
});

test('word at offset', function () {
    $input = <<<'EOT'
@if ('this')
    then
@endif
EOT;
    $doc = $this->getDocument($input);

    expect($doc->getWordAtOffset(6))->toBe('this');
    expect($doc->getWordAtOffset(7))->toBe('this');
    expect($doc->getWordAtOffset(8))->toBe('this');
    expect($doc->getWordAtOffset(9))->toBe('this');

    expect($doc->getWordAtOffset(1))->toBe('if');
    expect($doc->getWordAtOffset(2))->toBe('if');
    expect($doc->getWordAtOffset(1, ['@']))->toBe('@if');
    expect($doc->getWordAtOffset(2, ['@']))->toBe('@if');
    expect($doc->getWordAtOffset(0, ['@']))->toBe('@if');

    expect($doc->getWordAtOffset(23))->toBe('endif');
    expect($doc->getWordAtOffset(24))->toBe('endif');
    expect($doc->getWordAtOffset(25))->toBe('endif');
    expect($doc->getWordAtOffset(26))->toBe('endif');
    expect($doc->getWordAtOffset(27))->toBe('endif');
});

test('document get lines', function () {
    $input = <<<'EOT'
@if ('this')
    then
@endif
EOT;
    $doc = $this->getDocument($input);
    $lines = [
        "@if ('this')",
        '    then',
        '@endif',
    ];

    expect($doc->getLines())->toBe($lines);
});

test('get word left at offset', function () {
    $input = <<<'EOT'
One Two-Three
EOT;
    $doc = $this->getDocument($input);

    expect($doc->getWordLeftAtOffset(0))->toBeNull();
    expect($doc->getWordLeftAtOffset(1))->toBeNull();
    expect($doc->getWordLeftAtOffset(2))->toBeNull();

    expect($doc->getWordLeftAtOffset(4))->toBe('One');
    expect($doc->getWordLeftAtOffset(5))->toBe('One');
    expect($doc->getWordLeftAtOffset(6))->toBe('One');
    expect($doc->getWordLeftAtOffset(7))->toBe('One');

    expect($doc->getWordLeftAtOffset(8, []))->toBe('Two');
    expect($doc->getWordLeftAtOffset(9, []))->toBe('Two');
    expect($doc->getWordLeftAtOffset(10, []))->toBe('Two');
    expect($doc->getWordLeftAtOffset(11, []))->toBe('Two');
    expect($doc->getWordLeftAtOffset(12, []))->toBe('Two');

    expect($doc->getWordLeftAtOffset(8))->toBe('One');
    expect($doc->getWordLeftAtOffset(9))->toBe('One');
    expect($doc->getWordLeftAtOffset(10))->toBe('One');
    expect($doc->getWordLeftAtOffset(11))->toBe('One');
    expect($doc->getWordLeftAtOffset(12))->toBe('One');
});

test('get word right at offset', function () {
    $input = <<<'EOT'
One Two-Three
EOT;
    $doc = $this->getDocument($input);

    expect($doc->getWordRightAtOffset(0))->toBe('Two-Three');
    expect($doc->getWordRightAtOffset(1))->toBe('Two-Three');
    expect($doc->getWordRightAtOffset(2))->toBe('Two-Three');
    expect($doc->getWordRightAtOffset(3))->toBe('Two-Three');

    for ($i = 4; $i <= 12; $i++) {
        expect($doc->getWordRightAtOffset($i))->toBeNull();
    }

    expect($doc->getWordRightAtOffset(4, []))->toBe('Three');
    expect($doc->getWordRightAtOffset(5, []))->toBe('Three');
    expect($doc->getWordRightAtOffset(6, []))->toBe('Three');
    expect($doc->getWordRightAtOffset(7, []))->toBe('Three');
});

test('get line excerpt', function () {
    $input = <<<'EOT'
One
Two
Three
Four
Five
Six
Seven
Eight
EOT;
    $doc = $this->getDocument($input);

    expect($doc->getLineExcerpt(5))->toBe([
        3 => 'Three',
        4 => 'Four',
        5 => 'Five',
        6 => 'Six',
        7 => 'Seven',
    ]);

    expect($doc->getLineExcerpt(1))->toBe([
        1 => 'One',
        2 => 'Two',
        3 => 'Three',
    ]);

    expect($doc->getLineExcerpt(8, 4))->toBe([
        4 => 'Four',
        5 => 'Five',
        6 => 'Six',
        7 => 'Seven',
        8 => 'Eight',
    ]);
});
