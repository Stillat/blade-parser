<?php

namespace Stillat\BladeParser\Compiler\CompilerServices;

class LiteralContentHelpers
{
    /**
     * Reverses Blade literal content escape sequences in the provided content.
     *
     * @param  string  $content The content.
     */
    public static function getUnescapedContent(string $content): string
    {
        return strtr($content, [
            '@@' => '@',
            '@{!!' => '{!!',
            '@{{{' => '{{{',
            '@{{' => '{{',
        ]);
    }
}
