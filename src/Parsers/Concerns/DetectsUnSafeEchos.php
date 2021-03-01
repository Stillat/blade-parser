<?php

namespace Stillat\BladeParser\Parsers\Concerns;

trait DetectsUnSafeEchos
{
    private function isStartingUnsafeEcho()
    {
        if ($this->next === null) {
            return false;
        }

        if ($this->previous !== null) {
            if ($this->previous === self::TOKEN_ESCAPE_BLADE) {
                return false;
            }
        }

        if ($this->next !== null && $this->next === self::TOKEN_ECHO_UNSAFE) {
            $peek = $this->peek(3);

            if ($peek === null) {
                return false;
            }

            if ($peek === '{!!') {
                return true;
            }
        }

        return false;
    }
}