<?php

namespace Stillat\BladeParser\Document\Concerns;

use Illuminate\Support\Str;
use Stillat\BladeParser\Compiler\CompilerServices\StringUtilities;
use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Nodes\DirectiveNode;

trait ManagesTextExtraction
{
    /**
     * Returns the original document text between two character offsets.
     *
     * @param  int  $startOffset  The start offset.
     * @param  int  $endOffset  The end offset.
     */
    public function getText(int $startOffset, int $endOffset): string
    {
        return mb_substr($this->nodeText, $startOffset, $endOffset - $startOffset);
    }

    private function arePositionsValid(DirectiveNode|ComponentNode $node): bool
    {
        if ($node->position == null || $node->isClosedBy == null || $node->isClosedBy->position == null) {
            return false;
        }
        if ($node->isClosedBy->position->startOffset < $node->position->endOffset) {
            return false;
        }

        return true;
    }

    /**
     * Retrieves the inner document text between a node pair.
     *
     * @param  DirectiveNode|ComponentNode  $node  The node.
     */
    public function getPairedNodeInnerDocumentText(DirectiveNode|ComponentNode $node): string
    {
        if (! $this->arePositionsValid($node)) {
            return '';
        }

        return $this->getText($node->position->endOffset + 1, $node->isClosedBy->position->startOffset);
    }

    /**
     * Retrieves the outer document text for a given node pair.
     *
     * @param  DirectiveNode|ComponentNode  $node  The node.
     */
    public function getPairedNodeOuterDocumentText(DirectiveNode|ComponentNode $node): string
    {
        if (! $this->arePositionsValid($node)) {
            return '';
        }

        return $this->getText($node->position->startOffset, $node->isClosedBy->position->endOffset + 1);
    }

    /**
     * Returns the number of lines in the document.
     */
    public function getLineCount(): int
    {
        $count = mb_substr_count($this->nodeText, "\n");

        if ($count == 0) {
            return 1;
        }

        return $count;
    }

    /**
     * Retrieves the line number for the provided character offset.
     *
     * @param  int  $offset  The character offset.
     */
    public function getLineNumberFromOffset(int $offset): int
    {
        return mb_substr_count(str($this->nodeText)->substr(0, $offset), "\n") + 1;
    }

    /**
     * Returns the column number for the provided character offset.
     *
     * @param  int  $offset  The character offset.
     */
    public function getColumnNumberFromOffset(int $offset): int
    {
        $lastNlPosition = mb_strrpos(
            str($this->nodeText)->substr(0, $offset), "\n"
        );

        if ($lastNlPosition === false) {
            return $offset + 1;
        }

        return $offset - $lastNlPosition;
    }

    private function isValidDocumentOffset(int $offset): bool
    {
        if ($this->docString == null) {
            return false;
        }
        if ($offset < 0 || $offset > $this->docString->count()) {
            return false;
        }

        return true;
    }

    /**
     * Retrieves a word from the source document at the provided character offset.
     *
     * @param  int  $offset  The character offset.
     * @param  array  $chars  A list of characters that won't break a word.
     */
    public function getWordAtOffset(int $offset, array $chars = ['-']): ?string
    {
        if (! $this->isValidDocumentOffset($offset)) {
            return null;
        }
        $posChar = $this->docString[$offset];

        if (! in_array($posChar, $chars) &&
            (ctype_space($posChar) || ctype_punct($posChar))) {
            return null;
        }

        $leadingChars = '';
        $trailingChars = '';

        for ($i = $offset; $i >= 0; $i--) {
            $char = $this->docString[$i];

            if (in_array($char, $chars)) {
                $leadingChars .= $char;

                continue;
            }

            if (ctype_space($char) || ctype_punct($char)) {
                break;
            }

            $leadingChars .= $char;
        }

        if ($offset + 1 < count($this->docString)) {
            for ($i = $offset + 1; $i < count($this->docString); $i++) {
                $char = $this->docString[$i];

                if ($char == null) {
                    break;
                }

                if (in_array($char, $chars)) {
                    $trailingChars .= $char;

                    continue;
                }

                if (ctype_space($char) || ctype_punct($char)) {
                    break;
                }

                $trailingChars .= $char;
            }
        }

        return Str::reverse($leadingChars).$trailingChars;
    }

    /**
     * Returns the closest word character offset to the left of the provided character offset.
     *
     * @param  int  $offset  The character offset.
     * @param  array  $chars  A list of characters that won't break a word.
     */
    public function getNextWordPositionLeftAtOffset(int $offset, array $chars = ['-']): ?int
    {
        if (! $this->isValidDocumentOffset($offset)) {
            return null;
        }

        $exitedInitialWord = false;

        for ($i = $offset; $i >= 0; $i--) {
            $char = $this->docString[$i];

            if (in_array($char, $chars)) {
                if ($exitedInitialWord) {
                    return $this->docString->getPositionFromCharIndex($i);
                }

                continue;
            }

            if (ctype_space($char) || ctype_punct($char)) {
                $exitedInitialWord = true;

                continue;
            }

            if ($exitedInitialWord) {
                return $this->docString->getPositionFromCharIndex($i);
            }
        }

        return null;
    }

    /**
     * Retrieves the nearest word character offset to the right of the provided character offset.
     *
     * @param  int  $offset  The character offset.
     * @param  array  $chars  A list of characters that won't break a word.
     */
    public function getNextWordPositionRightAtOffset(int $offset, array $chars = ['-']): ?int
    {
        if (! $this->isValidDocumentOffset($offset)) {
            return null;
        }

        $exitedInitialWord = false;

        for ($i = $offset; $i < count($this->docString); $i++) {
            $char = $this->docString[$i];

            if (in_array($char, $chars)) {
                if ($exitedInitialWord) {
                    return $this->docString->getPositionFromCharIndex($i);
                }

                continue;
            }

            if (ctype_space($char) || ctype_punct($char)) {
                $exitedInitialWord = true;

                continue;
            }

            if ($exitedInitialWord) {
                return $this->docString->getPositionFromCharIndex($i);
            }
        }

        return null;
    }

    /**
     * Retrieves the word to the left of the provided offset, ignoring the word at the provided offset.
     *
     * @param  int  $offset  The character offset.
     * @param  array  $chars  A list of characters that won't break a word.
     */
    public function getWordLeftAtOffset(int $offset, array $chars = ['-']): ?string
    {
        $scanOffset = $this->getNextWordPositionLeftAtOffset($offset, $chars);

        if ($scanOffset == null) {
            return null;
        }

        return $this->getWordAtOffset($scanOffset, $chars);
    }

    /**
     * Retrieves the word to the right of the provided offset, ignoring the word at the provided offset.
     *
     * @param  int  $offset  The character offset.
     * @param  array  $chars  A list of characters that won't break a word.
     */
    public function getWordRightAtOffset(int $offset, array $chars = ['-']): ?string
    {
        $scanOffset = $this->getNextWordPositionRightAtOffset($offset, $chars);

        if ($scanOffset == null) {
            return null;
        }

        return $this->getWordAtOffset($scanOffset, $chars);
    }

    /**
     * Retrieves the requested line number, and a specified number of lines surrounding it.
     *
     * The keys of the returned array will correspond
     * to the line number the text was extracted from.
     *
     * @param  int  $lineNumber  The target line number.
     * @param  int  $radius  The number of desired lines surrounding the target line number.
     * @return string[]
     */
    public function getLineExcerpt(int $lineNumber, int $radius = 2): array
    {
        if ($lineNumber < 0) {
            return [];
        }
        $lines = $this->getLines();
        $lineCount = count($lines);

        if ($lineNumber > $lineCount) {
            return [];
        }

        // Subtract one to convert our one-based
        // line number into a zero-based index.
        $lineNumber -= 1;

        $minLine = $lineNumber - $radius;
        $maxLine = $lineNumber + $radius;

        // Ensure or min and max lines stay within bounds.
        if ($minLine < 0) {
            $minLine = 0;
        }
        if ($maxLine >= $lineCount) {
            $maxLine = $lineCount - 1;
        }

        $returnLines = [];

        for ($i = $minLine; $i <= $maxLine; $i++) {
            $returnLines[$i + 1] = $lines[$i];
        }

        return $returnLines;
    }

    /**
     * Retrieves a string array, containing the lines of the source document.
     *
     * @return string[]
     */
    public function getLines(): array
    {
        return StringUtilities::breakByNewLine($this->nodeText);
    }
}
