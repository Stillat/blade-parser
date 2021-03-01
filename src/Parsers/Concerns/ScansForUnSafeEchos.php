<?php

namespace Stillat\BladeParser\Parsers\Concerns;

trait ScansForUnSafeEchos
{
    private function scanToEndOfUnsafeEcho($start)
    {
        $echo = '';
        $breakIndex = $start;

        for ($i = ($start); $i < $this->tokenLength; $i++) {
            $current = $this->tokens[$i];
            $nextIndex = $i + 1;
            $next = null;

            if ($nextIndex < $this->tokenLength) {
                $next = $this->tokens[$nextIndex];
            }

            if ($current === self::TOKEN_ECHO_UNSAFE) {
                $peek = $this->indexPeek($i, 2);

                if ($peek === '!}') {

                    $echo .= '!}';
                    $breakIndex = ($i + 2);
                    break;
                }
            }

            $breakIndex+=1;
            $echo .= $current;
        }

        return [
            $echo,
            $breakIndex,
            1
        ];
    }

}