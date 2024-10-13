<?php

namespace Stillat\BladeParser\Parser\HtmlFragments;

use Stillat\BladeParser\Compiler\CompilerServices\StringUtilities;
use Stillat\BladeParser\Nodes\Fragments\Fragment;
use Stillat\BladeParser\Nodes\Fragments\HtmlFragment;
use Stillat\BladeParser\Nodes\NodeIndexer;
use Stillat\BladeParser\Nodes\Position;
use Stillat\BladeParser\Parser\DocumentParser;
use Stillat\BladeParser\Support\Utf8StringIterator;

class FragmentsDocumentParser extends BaseFragmentParser
{
    private FragmentAttributeParser $attributeParser;

    protected string $sourceContent = '';

    protected array $documentOffsets = [];

    /**
     * The HTML fragments within the document.
     *
     * @var HtmlFragment[]
     */
    private array $fragments = [];

    /**
     * A list of offsets to skip.
     */
    protected array $ignoreRanges = [];

    /**
     * Indicates if the parser is inside a <script> element.
     */
    private bool $isParsingScript = false;

    public function __construct()
    {
        $this->attributeParser = new FragmentAttributeParser;
    }

    /**
     * Sets the internal ranges to ignore while parsing.
     *
     * @param  array  $ignoreRanges  The ranges to ignore.
     */
    public function setIgnoreRanges(array $ignoreRanges): void
    {
        $this->ignoreRanges = $ignoreRanges;
    }

    /**
     * Parses an HTML fragment at the provided index.
     *
     * @param  int  $index  The location to begin parsing.
     */
    private function parseFragment(int $index): void
    {
        $this->buffer = '';
        $brokeEarly = false;

        for ($i = $index; $i < count($this->string); $i++) {
            if (array_key_exists($i, $this->ignoreRanges)) {
                $jumpIndex = $this->ignoreRanges[$i];

                $this->buffer .= str(' ')->repeat($jumpIndex - $i + 1);

                $i = $jumpIndex;

                continue;
            }
            $this->checkCurrentOffsets($i);

            if (($i != $index && $this->current == '<') ||
                ($this->current != '>' && $this->next == null)) {
                $brokeEarly = true;
                break;
            }

            if ($this->isStartOfString()) {
                $i = $this->scanToEndOfString($i);

                continue;
            }

            if ($this->current == '>') {
                $this->buffer .= $this->current;
                break;
            }

            $this->buffer .= $this->current;
        }

        if ($brokeEarly) {
            $this->buffer = '';

            return;
        }

        $fragment = new HtmlFragment;
        $fragment->position = $this->makePosition($index, $this->position);
        $fragment->content = $this->buffer;
        $fragment->isSelfClosing = str($this->buffer)->endsWith('/>');
        $fragment->isClosingTag = str($this->buffer)->startsWith('</');

        $documentContentStartOffset = 1;
        $documentContentEndOffset = -1;

        if ($fragment->isClosingTag) {
            $documentContentStartOffset = 2;
        }

        if ($fragment->isSelfClosing) {
            $documentContentEndOffset = -2;
        }

        $fragment->documentContent =
            str($this->buffer)->substr(
                $documentContentStartOffset,
                $documentContentEndOffset
            );

        // Retrieve all content before the first whitespace character.
        $fragment->tagName = trim(StringUtilities::beforeFirstWhitespace($fragment->documentContent));

        // Check if the tag name was an ignored region.
        // If so, we can grab that content substring.
        if (array_key_exists(
            $fragment->position->startOffset + $documentContentStartOffset, $this->ignoreRanges
        )) {
            $tagNameStart = $fragment->position->startOffset + $documentContentStartOffset;

            $tagNameEnd = $this->ignoreRanges[$tagNameStart];
            $nameLength = $tagNameEnd - $tagNameStart + 1;
            $fragment->tagName = str($this->string)->substr($tagNameStart, $nameLength)->value();
        }

        // Create a Fragment representing the name.
        if (str($fragment->tagName)->trim()->length > 0) {
            $fragment->name = new Fragment;
            $fragment->name->content = $fragment->tagName;
            $fragment->name->position->startOffset = $fragment->position->startOffset + $documentContentStartOffset;

            $fragment->name->position->endOffset = $fragment->name->position->startOffset + str($fragment->tagName)->length() - 1;
        }

        // Calculate the start of the inner content.
        // This will be the first space after the tag name.
        $innerContentStart = StringUtilities::firstWhitespacePos($fragment->documentContent) ?? false;

        if ($innerContentStart !== false) {
            $innerContentFragment = new Fragment;
            $innerContentFragment->content = str($fragment->documentContent)->substr($innerContentStart)->trim();

            // Calculate the start and end positions of the
            // inner content relative to the document.
            $innerContentFragment->position->startOffset = mb_strpos($fragment->documentContent, $innerContentFragment->content) + 1 + $fragment->position->startOffset;

            if ($fragment->isClosingTag) {
                $innerContentFragment->position->startOffset += 1;
            }

            $innerContentFragment->position->endOffset =
                $innerContentFragment->position->startOffset +
                str($innerContentFragment->content)->length();

            $fragment->innerContent = $innerContentFragment;
        }

        $this->parseAttributes($fragment);

        if (! $fragment->isClosingTag && ! $fragment->isSelfClosing && str($fragment->tagName)->lower == 'script') {
            $this->isParsingScript = true;
        }

        $this->fragments[] = $fragment;
    }

    /**
     * Parses attributes within the provided fragment.
     */
    private function parseAttributes(HtmlFragment $fragment): void
    {
        if ($fragment->innerContent == null ||
            str($fragment->innerContent->content)->length() == 0) {
            return;
        }

        $fragment->parameters = $this->attributeParser
            ->parse($fragment);
    }

    private function buildFragmentIndex(): array
    {
        preg_match_all('/</', $this->string, $matches, PREG_OFFSET_CAPTURE);

        $fragmentStarts = [];

        foreach ($matches[0] as $match) {
            $index = $match[1];
            $isValid = true;

            foreach ($this->ignoreRanges as $rangeStart => $rangeEnd) {
                if ($index >= $rangeStart && $index <= $rangeEnd) {
                    $isValid = false;
                    break;
                }
            }

            if ($isValid) {
                $fragmentStarts[] = $index;
            }
        }

        return $fragmentStarts;
    }

    /**
     * Returns the parsed fragments.
     *
     * @return HtmlFragment[]
     */
    public function getFragments(): array
    {
        return $this->fragments;
    }

    /**
     * Parses the input value and returns a list of fragments.
     *
     * @param  string  $value  The value to parse.
     * @return HtmlFragment[]
     */
    public function parse(string $value): array
    {
        $this->isParsingScript = false;
        $this->sourceContent = StringUtilities::normalizeLineEndings($value);
        $this->string = new Utf8StringIterator($this->sourceContent);

        // The document content was normalized, so we can search for "\n".
        preg_match_all('/\n/', $this->sourceContent, $documentNewLines, PREG_OFFSET_CAPTURE);
        $newLineCountLen = count($documentNewLines[0]);

        $currentLine = 1;
        $lastOffset = null;
        for ($i = 0; $i < $newLineCountLen; $i++) {
            $thisNewLine = $documentNewLines[0][$i];
            $thisIndex = $thisNewLine[1];
            $indexChar = $thisIndex;

            if ($lastOffset != null) {
                $indexChar = $thisIndex - $lastOffset;
            } else {
                $indexChar = $indexChar + 1;
            }

            $this->documentOffsets[$thisIndex] = [
                DocumentParser::K_CHAR => $indexChar,
                DocumentParser::K_LINE => $currentLine,
            ];

            $currentLine += 1;
            $lastOffset = $thisIndex;
        }

        // Reduce the start positions by removing
        // those that are contained within the
        // list of offsets to be ignored.
        $fragmentStartIndexes = $this->buildFragmentIndex();
        $fragmentCount = count($fragmentStartIndexes);

        for ($i = 0; $i < $fragmentCount; $i++) {
            $this->parseFragment($fragmentStartIndexes[$i]);
            $this->resetState();

            if ($this->isParsingScript) {
                for ($j = $i + 1; $j < $fragmentCount; $j++) {
                    $start = $fragmentStartIndexes[$j];
                    $check = str($this->string)->substr($start, 8)->lower();

                    if ($check == '</script') {
                        $this->isParsingScript = false;
                        $i = $j - 1;
                        break;
                    }
                }
            }
        }

        $this->fillColumnAndLineNumbers();

        return $this->fragments;
    }

    protected function lineColumnFromOffset($offset): array
    {
        if (count($this->documentOffsets) == 0) {
            return [
                DocumentParser::K_LINE => 1,
                DocumentParser::K_CHAR => $offset + 1,
            ];
        }

        $lineToUse = 0;
        $charToUse = 0;

        if (! array_key_exists($offset, $this->documentOffsets)) {
            $nearestOffset = null;
            $nearestOffsetIndex = null;

            foreach ($this->documentOffsets as $documentOffset => $details) {
                if ($documentOffset >= $offset) {
                    $nearestOffset = $details;
                    $nearestOffsetIndex = $documentOffset;
                    break;
                }
            }

            if ($nearestOffset != null) {
                $offsetDelta = $nearestOffset[DocumentParser::K_CHAR] - $nearestOffsetIndex + $offset;
                $charToUse = $offsetDelta;
                $lineToUse = $nearestOffset[DocumentParser::K_LINE];
            } else {
                $lastOffsetKey = array_key_last($this->documentOffsets);
                $lastOffset = $this->documentOffsets[$lastOffsetKey];
                $lineToUse = $lastOffset['line'] + 1;
                $charToUse = $offset - $lastOffsetKey;
            }
        } else {
            $details = $this->documentOffsets[$offset];

            $lineToUse = $details[DocumentParser::K_LINE];
            $charToUse = $details[DocumentParser::K_CHAR];
        }

        return [
            DocumentParser::K_LINE => $lineToUse,
            DocumentParser::K_CHAR => $charToUse,
        ];
    }

    private function setColumnAndLineNumber(Position $position): void
    {
        $start = $this->lineColumnFromOffset($position->startOffset);
        $position->startColumn = $start[DocumentParser::K_CHAR];
        $position->startLine = $start[DocumentParser::K_LINE];

        $end = $this->lineColumnFromOffset($position->endOffset);
        $position->endColumn = $end[DocumentParser::K_CHAR];
        $position->endLine = $end[DocumentParser::K_LINE];
    }

    private function fillColumnAndLineNumbers()
    {
        foreach ($this->fragments as $fragment) {
            if ($fragment->position != null) {
                $this->setColumnAndLineNumber($fragment->position);
            }

            if ($fragment->innerContent != null && $fragment->innerContent->position != null) {
                $this->setColumnAndLineNumber($fragment->innerContent->position);
            }

            if ($fragment->name != null && $fragment->name->position != null) {
                $this->setColumnAndLineNumber($fragment->name->position);
            }

            if (count($fragment->parameters) > 0) {
                NodeIndexer::indexNodes($fragment->parameters);
                foreach ($fragment->parameters as $param) {
                    if ($param->position != null) {
                        $this->setColumnAndLineNumber($param->position);
                    }
                }
            }
        }
    }

    private function makePosition(int $startOffset, int $endOffset): Position
    {
        $position = new Position;
        $position->startOffset = $startOffset;
        $position->endOffset = $endOffset;

        return $position;
    }
}
