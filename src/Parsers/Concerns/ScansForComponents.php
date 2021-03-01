<?php

namespace Stillat\BladeParser\Parsers\Concerns;

trait ScansForComponents
{
    private function scanToEndOfComponent($start)
    {
        $component = '';
        $breakIndex = $start;
        $scannedChars = 0;

        for ($i = $start; $i < $this->tokenLength; $i++) {
            $cur = $this->tokens[$i];
            $nextIndex = $i + 1;
            $next = null;
            if ($nextIndex < $this->tokenLength) {
                $next = $this->tokens[$nextIndex];
            }

            if ($cur === self::TOKEN_COMPONENT_SELF_CLOSE_START && (
                    $next !== null && $next === self::TOKEN_COMPONENT_SELF_CLOSE_END
                )) {
                $component .= '/>';
                $breakIndex = $i + 1;
                break;
            } else {
                $component .= $cur;
            }

            $scannedChars += 1;
        }

        return [$component, $breakIndex];
    }
}
