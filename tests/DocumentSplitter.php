<?php

namespace Stillat\BladeParser\Tests;

use Stillat\BladeParser\Compiler\CompilerServices\StringUtilities;

class DocumentSplitter
{
    public static function splitDocumentOnNewLines(string $doc): array
    {
        $parts = [];
        $lines = StringUtilities::breakByNewLine($doc);
        $template = '';

        foreach ($lines as $line) {
            $template .= $line;
            $parts[] = [$template];
        }

        return $parts;
    }

    public static function splitDocumentOnChar(string $doc): array
    {
        $parts = [];
        $chars = mb_str_split($doc);
        $template = '';

        foreach ($chars as $char) {
            $template .= $char;
            $parts[] = [$template];
        }

        return $parts;
    }
}
