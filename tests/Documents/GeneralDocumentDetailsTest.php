<?php

namespace Stillat\BladeParser\Tests\Documents;

use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Tests\ParserTestCase;

class GeneralDocumentDetailsTest extends ParserTestCase
{
    public function testFilePathCanBeSet()
    {
        $document = Document::fromText('Simple template', 'resources/views/layout.validation.php');
        $this->assertSame('resources/views/layout.validation.php', $document->getFilePath());
    }

    public function testDocumentToDocumentDoesNotReturnSameInstance()
    {
        $input = <<<'EOT'
@if ($this)
    @else ($that)
        @endif
EOT;
        $doc = $this->getDocument($input);
        $doc2 = $doc->toDocument();
        $this->assertNotNull($doc2);
        $this->assertNotSame($doc, $doc2);

        $this->assertSame($input, (string) $doc);
        $this->assertSame((string) $doc, (string) $doc2);
    }

    public function testDocumentTextExtraction()
    {
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
        $this->assertSame($expected, $doc->extractText());

        $expected = <<<'EXPECTED'
A@@include ('one')B
    CD
        E@@include ('three')F
EXPECTED;
        $this->assertSame($expected, $doc->extractText(false));
    }

    public function testDocumentsReleaseNodes()
    {
        $input = <<<'EOT'
@if ($this)
    @else ($that)
        @endif
EOT;
        $doc = $this->getDocument($input);
        $nodes = $doc->getNodes();

        /** @var AbstractNode $node */
        foreach ($nodes as $node) {
            $this->assertTrue($node->hasDocument());
            $this->assertSame($doc, $node->getDocument());
        }

        $doc->releaseNodesFromDocument();

        /** @var AbstractNode $node */
        foreach ($nodes as $node) {
            $this->assertFalse($node->hasDocument());
            $this->assertNull($node->getDocument());
        }
    }

    public function testDocumentLineCount()
    {
        $this->assertSame(1, $this->getDocument('')->getLineCount());
        $this->assertSame(3, $this->getDocument(str_repeat("\n", 3))->getLineCount());
    }

    public function testLineNumberFromOffset()
    {
        $input = <<<'EOT'
@if ('this')
    then
@endif
EOT;
        $doc = $this->getDocument($input);

        $line = 1;
        for ($i = 0; $i < strlen($input); $i++) {
            $this->assertSame($line, $doc->getLineNumberFromOffset($i));
            if ($input[$i] == "\n") {
                $line += 1;
            }
        }
    }

    public function testColumnNumberFromOffset()
    {
        $input = <<<'EOT'
@if ('this')
    then
@endif
EOT;
        $doc = $this->getDocument($input);

        $column = 1;
        for ($i = 0; $i < strlen($input); $i++) {
            $this->assertSame($column, $doc->getColumnNumberFromOffset($i));
            if ($input[$i] == "\n") {
                $column = 1;
            } else {
                $column += 1;
            }
        }
    }

    public function testWordAtOffset()
    {
        $input = <<<'EOT'
@if ('this')
    then
@endif
EOT;
        $doc = $this->getDocument($input);

        $this->assertSame('this', $doc->getWordAtOffset(6));
        $this->assertSame('this', $doc->getWordAtOffset(7));
        $this->assertSame('this', $doc->getWordAtOffset(8));
        $this->assertSame('this', $doc->getWordAtOffset(9));

        $this->assertSame('if', $doc->getWordAtOffset(1));
        $this->assertSame('if', $doc->getWordAtOffset(2));
        $this->assertSame('@if', $doc->getWordAtOffset(1, ['@']));
        $this->assertSame('@if', $doc->getWordAtOffset(2, ['@']));
        $this->assertSame('@if', $doc->getWordAtOffset(0, ['@']));

        $this->assertSame('endif', $doc->getWordAtOffset(23));
        $this->assertSame('endif', $doc->getWordAtOffset(24));
        $this->assertSame('endif', $doc->getWordAtOffset(25));
        $this->assertSame('endif', $doc->getWordAtOffset(26));
        $this->assertSame('endif', $doc->getWordAtOffset(27));
    }

    public function testDocumentGetLines()
    {
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

        $this->assertSame($lines, $doc->getLines());
    }

    public function testGetWordLeftAtOffset()
    {
        $input = <<<'EOT'
One Two-Three
EOT;
        $doc = $this->getDocument($input);

        $this->assertNull($doc->getWordLeftAtOffset(0));
        $this->assertNull($doc->getWordLeftAtOffset(1));
        $this->assertNull($doc->getWordLeftAtOffset(2));

        $this->assertSame('One', $doc->getWordLeftAtOffset(4));
        $this->assertSame('One', $doc->getWordLeftAtOffset(5));
        $this->assertSame('One', $doc->getWordLeftAtOffset(6));
        $this->assertSame('One', $doc->getWordLeftAtOffset(7));

        $this->assertSame('Two', $doc->getWordLeftAtOffset(8, []));
        $this->assertSame('Two', $doc->getWordLeftAtOffset(9, []));
        $this->assertSame('Two', $doc->getWordLeftAtOffset(10, []));
        $this->assertSame('Two', $doc->getWordLeftAtOffset(11, []));
        $this->assertSame('Two', $doc->getWordLeftAtOffset(12, []));

        $this->assertSame('One', $doc->getWordLeftAtOffset(8));
        $this->assertSame('One', $doc->getWordLeftAtOffset(9));
        $this->assertSame('One', $doc->getWordLeftAtOffset(10));
        $this->assertSame('One', $doc->getWordLeftAtOffset(11));
        $this->assertSame('One', $doc->getWordLeftAtOffset(12));
    }

    public function testGetWordRightAtOffset()
    {
        $input = <<<'EOT'
One Two-Three
EOT;
        $doc = $this->getDocument($input);

        $this->assertSame('Two-Three', $doc->getWordRightAtOffset(0));
        $this->assertSame('Two-Three', $doc->getWordRightAtOffset(1));
        $this->assertSame('Two-Three', $doc->getWordRightAtOffset(2));
        $this->assertSame('Two-Three', $doc->getWordRightAtOffset(3));

        for ($i = 4; $i <= 12; $i++) {
            $this->assertNull($doc->getWordRightAtOffset($i));
        }

        $this->assertSame('Three', $doc->getWordRightAtOffset(4, []));
        $this->assertSame('Three', $doc->getWordRightAtOffset(5, []));
        $this->assertSame('Three', $doc->getWordRightAtOffset(6, []));
        $this->assertSame('Three', $doc->getWordRightAtOffset(7, []));
    }

    public function testGetLineExcerpt()
    {
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

        $this->assertSame([
            3 => 'Three',
            4 => 'Four',
            5 => 'Five',
            6 => 'Six',
            7 => 'Seven',
        ], $doc->getLineExcerpt(5));

        $this->assertSame([
            1 => 'One',
            2 => 'Two',
            3 => 'Three',
        ], $doc->getLineExcerpt(1));

        $this->assertSame([
            4 => 'Four',
            5 => 'Five',
            6 => 'Six',
            7 => 'Seven',
            8 => 'Eight',
        ], $doc->getLineExcerpt(8, 4));
    }
}
