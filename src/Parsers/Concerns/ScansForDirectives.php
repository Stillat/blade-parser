<?php

namespace Stillat\BladeParser\Parsers\Concerns;

use Stillat\BladeParser\Parsers\Blade;
use Stillat\BladeParser\Parsers\Structures\PhpBlockParser;
use Stillat\BladeParser\Parsers\Structures\VerbatimBlockParser;

trait ScansForDirectives
{
    private function scanToEndOfDirective($start)
    {
        $part = '';
        $breakIndex = $start;
        $scannedChars = 0;
        $directiveName = '';
        $hasFoundDirectiveName = false;
        $directiveInnerContents = '';
        $currentOpenDirectiveTokens = 0;
        $exitedOnNewLine = false;

        for ($i = $start; $i < $this->tokenLength; $i++) {
            $cur = $this->tokens[$i];
            $nextIndex = $i + 1;
            $next = null;

            if ($nextIndex < $this->tokenLength) {
                $next = $this->tokens[$nextIndex];
            }

            if ($this->isStartOfString($cur)) {
                $stringDetails = $this->scanToEndOfString($i);

                $part .= $stringDetails[0];
                $i = ($stringDetails[2]);

                $directiveInnerContents .= $stringDetails[0];

                $breakIndex += 1;
                continue;
            }

            if ($cur === self::TOKEN_BLADE_DIRECTIVE_INPUT_START) {
                $hasFoundDirectiveName = true;
            }

            if ($cur === self::TOKEN_BLADE_DIRECTIVE_INPUT_START && $hasFoundDirectiveName) {
                $currentOpenDirectiveTokens += 1;
            }

            if ($currentOpenDirectiveTokens == 0 &&
                ($cur === self::TOKEN_BLADE_DIRECTIVE_END ||
                    $cur === self::TOKEN_LINE_SEPARATOR)) {
                $part .= $cur;
                $breakIndex = $i;

                if ($cur === self::TOKEN_LINE_SEPARATOR) {
                    $exitedOnNewLine = true;
                }

                break;
            }

            if ($cur === self::TOKEN_BLADE_DIRECTIVE_END) {
                $currentOpenDirectiveTokens -= 1;

                if ($currentOpenDirectiveTokens === 0 && $hasFoundDirectiveName) {
                    $part .= $cur;
                    $breakIndex = $i;

                    $directiveInnerContents .= $cur;

                    if ($cur === self::TOKEN_LINE_SEPARATOR) {
                        $exitedOnNewLine = true;
                    }

                    break;
                }
            }

            $part .= $cur;

            if ($hasFoundDirectiveName) {
                $directiveInnerContents .= $cur;
            }

            if ($scannedChars > 0 && $hasFoundDirectiveName === false) {
                $directiveName .= $cur;
            }

            $scannedChars += 1;
            $breakIndex += 1;
        }

        if (mb_strlen($directiveInnerContents) >= 2) {
            $directiveInnerContents = mb_substr($directiveInnerContents, 1);
            $directiveInnerContents = mb_substr($directiveInnerContents, 0, mb_strlen($directiveInnerContents) - 1);
        }

        if ($this->isVerbatimOpen($directiveName)) {
            $directiveName = VerbatimBlockParser::VERBATIM;
        } elseif ($this->isPhpOpen($directiveName)) {
            $directiveName = PhpBlockParser::NAME_PHP;
        }

        return [$part, $directiveName, $breakIndex, $directiveInnerContents, $exitedOnNewLine];
    }

    private function isPhpOpen($directiveName)
    {
        if (mb_strlen($directiveName) < 4) {
            return false;
        }

        $test = mb_substr($directiveName, 0, 4);

        if ($test == PhpBlockParser::NAME_PHP.' ') {
            return true;
        }

        return false;
    }

    private function isReplacedPhpExtraction($directiveName)
    {
        if (mb_strlen($directiveName) < 4) {
            return false;
        }

        $test = Blade::TOKEN_BLADE_START.mb_substr($directiveName, 0, 4);

        if ($test == Blade::TOKEN_BLADE_START.PhpBlockParser::NAME_PHP.'-') {
            return true;
        }

        return false;
    }

    private function isVerbatimOpen($directiveName)
    {
        if (mb_strlen($directiveName) < 8) {
            return false;
        }

        $test = mb_substr($directiveName, 0, 8);

        if ($test == VerbatimBlockParser::VERBATIM) {
            return true;
        }

        return false;
    }
}
