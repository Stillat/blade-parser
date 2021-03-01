<?php

namespace Stillat\BladeParser\Parsers\Concerns;

trait ScansForSafeEchos
{
    private function couldBeEscapedEcho()
    {
        if ($this->next === null) {
            return false;
        }

        if ($this->next !== null && $this->next === self::TOKEN_ECHO_START) {
            return true;
        }

        return false;
    }

    private function scanToEndOfEcho($start)
    {
        $echo = '{{';
        $breakIndex = $start + 2;
        $setFirst = false;
        $firstChar = null;
        $openCount = 2;

        for ($i = ($start + 2); $i < $this->tokenLength; $i++) {
            $current = $this->tokens[$i];
            $nextIndex = $i + 1;
            $next = null;

            if ($setFirst === false) {
                $firstChar = $current;
                $setFirst = true;
            }

            if ($nextIndex < $this->tokenLength) {
                $next = $this->tokens[$nextIndex];
            }

            if ($current === self::TOKEN_ECHO_END && (
                    $next !== null && $next === self::TOKEN_ECHO_END
                )) {
                if ($firstChar === self::TOKEN_ECHO_START && ($next === self::TOKEN_ECHO_END)) {
                    $echo .= '}}}';
                    $openCount = 3;
                    $breakIndex = ($i + 2);
                    break;
                }

                $echo .= '}}';
                $breakIndex = ($i + 1);

                break;
            }

            $echo .= $current;
        }

        return [
            $echo,
            $breakIndex,
            $openCount,
        ];
    }
}
