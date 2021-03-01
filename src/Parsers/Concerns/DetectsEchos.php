<?php

namespace Stillat\BladeParser\Parsers\Concerns;

trait DetectsEchos
{
    private function isStartingBladeEcho()
    {
        if ($this->next === null) {
            return false;
        }

        if ($this->previous !== null) {
            if ($this->previous === self::TOKEN_ESCAPE_BLADE) {
                return false;
            }
        }

        if ($this->next !== null && $this->next === self::TOKEN_ECHO_START) {
            return true;
        }

        return false;
    }

}