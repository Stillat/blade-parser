<?php

namespace Stillat\BladeParser\Parser\HtmlFragments;

use Stillat\BladeParser\Support\Utf8StringIterator;

abstract class BaseFragmentParser
{
    protected Utf8StringIterator $string;

    protected string $buffer = '';

    protected int $position = 0;

    protected ?string $current = null;

    protected ?string $next = null;

    protected ?string $prev = null;

    /**
     * Sets the internal state variables for the provided offset.
     */
    protected function checkCurrentOffsets(int $offset): void
    {
        $this->position = $offset;
        $this->current = $this->string[$offset] ?? null;
        $this->next = $this->string[$offset + 1] ?? null;
        $this->prev = $this->string[$offset - 1] ?? null;
    }

    /**
     * Resets the internal state variables.
     */
    protected function resetState(): void
    {
        $this->position = 0;
        $this->buffer = '';
        $this->next = null;
        $this->prev = null;
        $this->current = null;
    }

    /**
     * Tests if the current position is the start of a string.
     */
    protected function isStartOfString(): bool
    {
        return $this->current == '"' || $this->current == "'";
    }

    /**
     * Advances the parser over a string, and adds
     * the string's content to the internal buffer.
     *
     * @param  int  $start The start position.
     */
    protected function scanToEndOfString(int $start): int
    {
        $stringStyle = $this->current;
        $this->buffer .= $this->current;
        $returnedOn = $start;

        for ($i = $start + 1; $i < count($this->string); $i++) {
            $this->checkCurrentOffsets($i);

            $this->buffer .= $this->current;
            if ($this->current == $stringStyle) {
                $returnedOn = $i;
                break;
            }
        }

        return $returnedOn;
    }
}
