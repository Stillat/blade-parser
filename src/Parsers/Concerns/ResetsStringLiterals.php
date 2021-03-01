<?php

namespace Stillat\BladeParser\Parsers\Concerns;

trait ResetsStringLiterals
{

    private function convertSegmentToStringLiteral()
    {
        if (mb_strlen($this->currentSegment) > 0) {
            $this->directives[] = [
                'type' => 'literal',
                'content' => $this->currentSegment
            ];
        }

        $this->currentSegment = '';
    }

}
