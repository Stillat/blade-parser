<?php

namespace Stillat\BladeParser\Parsers\Concerns;

trait ScansForStrings
{

    private function isStartOfString($token)
    {
        if (in_array($token, $this->stringInitiators) && $this->isParsingString === false) {
            return true;
        }

        return false;
    }

    private function scanToEndOfString($start)
    {
        $stringStartsOn = $start;
        $stringEndsOn = -1;
        $startToken = $this->tokens[$start];
        $this->currentStringInitiator = $startToken;

        $stringValue = $startToken;

        $strCurrent = null;

        for ($i = ($start + 1); $i < $this->tokenLength; $i++) {
            $strCurrent = $this->tokens[$i];
            $nextIndex = $i + 1;
            $nextToken = null;

            if ($nextIndex < $this->tokenLength) {
                $nextToken = $this->tokens[$nextIndex];
            }

            if ($strCurrent === self::TOKEN_STRING_ESCAPE && ($nextToken !== null && $nextToken === $this->currentStringInitiator)) {
                $stringValue .= self::TOKEN_STRING_ESCAPE . $this->currentStringInitiator;
                $i += 1;
                continue;
            }

            if ($strCurrent === $this->currentStringInitiator) {
                $stringValue .= $this->currentStringInitiator;
                $stringEndsOn = $i;
                break;
            }

            $stringValue .= $strCurrent;
        }

        return [
            $stringValue,
            $stringStartsOn,
            $stringEndsOn
        ];
    }


}