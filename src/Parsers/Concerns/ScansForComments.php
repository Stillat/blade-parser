<?php

namespace Stillat\BladeParser\Parsers\Concerns;

use Stillat\BladeParser\Parsers\Blade;

trait ScansForComments
{
    /**
     * Tests if the current location is the start of a comment.
     *
     * @return bool
     */
    protected function isStartOfComment()
    {
        $peek = $this->peek(4);

        if ($peek !== null && $peek === Blade::COMMENT_START) {
            return true;
        }

        return false;
    }

    protected function scanToEndOfComment($start)
    {
        $breakIndex = $start + 4;
        $scannedChars = 0;
        $comment = Blade::COMMENT_START;

        for ($i = ($start + 4); $i < $this->tokenLength; $i++) {
            $current = $this->tokens[$i];
            $nextIndex = $i + 1;
            $next = null;

            if ($next < $this->tokenLength) {
                $next = $this->tokens[$nextIndex];
            }

            if ($current === Blade::TOKEN_COMMENT_DELIMITER) {
                $peek = $this->indexPeek($i, 4);

                if ($peek !== null && $peek === Blade::COMMENT_END) {
                    $breakIndex = $i + 3;
                    $comment .= Blade::COMMENT_END;
                    break;
                }
            }

            $breakIndex += 1;
            $scannedChars += 1;
            $comment .= $current;
        }

        return [
            $comment,
            $breakIndex,
        ];
    }
}
