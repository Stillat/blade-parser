<?php

namespace Stillat\BladeParser\Nodes;

class Position
{
    /**
     * The starting character offset.
     *
     * Starts at zero.
     */
    public int $startOffset = 0;

    /**
     * The starting line number.
     *
     * Starts at one.
     */
    public ?int $startLine = null;

    /**
     * The starting column number.
     *
     * Starts at one.
     */
    public ?int $startColumn = null;

    /**
     * The ending character offset.
     *
     * Starts at zero.
     */
    public int $endOffset = 0;

    /**
     * The ending line number.
     *
     * Starts at one.
     */
    public ?int $endLine = null;

    /**
     * The ending column number.
     *
     * Starts at one.
     */
    public ?int $endColumn = null;

    /**
     * Tests if the position contains the provided offset.
     *
     * @param  int  $offset  The offset to test.
     */
    public function contains(int $offset): bool
    {
        return $offset >= $this->startOffset && $offset <= $this->endOffset;
    }

    public function clone(): Position
    {
        $position = new Position();

        $position->startOffset = $this->startOffset;
        $position->startLine = $this->startLine;
        $position->startColumn = $this->startColumn;

        $position->endOffset = $this->endOffset;
        $position->endLine = $this->endLine;
        $position->endColumn = $this->endColumn;

        return $position;
    }
}
