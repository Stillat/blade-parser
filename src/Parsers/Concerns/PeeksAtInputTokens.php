<?php

namespace Stillat\BladeParser\Parsers\Concerns;

trait PeeksAtInputTokens
{
    private function peek($count)
    {
        if (($this->currentIndex + $count) < $this->tokenLength) {
            return implode(array_slice($this->tokens, $this->currentIndex, $count));
        }

        return null;
    }

    private function indexPeek($index, $count)
    {
        if (($index + $count - 1) < $this->tokenLength) {
            return implode(array_slice($this->tokens, $index, $count));
        }

        return null;
    }
}
