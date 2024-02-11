<?php

namespace Stillat\BladeParser\Compiler\CompilerServices;

use Illuminate\Support\Str;
use Stillat\BladeParser\Parser\AbstractParser;

class StringUtilities
{
    /**
     * Lowercases the first character of the input string.
     *
     * @param  string  $value  The value.
     */
    public static function lcfirst(string $value): string
    {
        return mb_strtolower(mb_substr($value, 0, 1)).mb_substr($value, 1);
    }

    /**
     * Safely wraps the provided value in the provided quote style, if it is not already.
     *
     * @param  string  $value  The value to wrap.
     * @param  string  $quoteStyle  The quote style. Supply either ' or "
     */
    public static function wrapInQuotes(string $value, string $quoteStyle): string
    {
        if (Str::startsWith($value, $quoteStyle) && Str::endsWith($value, $quoteStyle)) {
            return $value;
        }

        $quoteReplace = '_replace:'.Str::uuid();
        $value = str_replace('\\'.$quoteStyle, $quoteReplace, $value);
        $value = str_replace($quoteStyle, '\\'.$quoteStyle, $value);
        $value = str_replace($quoteReplace, '\\'.$quoteStyle, $value);

        return $quoteStyle.$value.$quoteStyle;
    }

    /**
     * Escapes single quotes within the provided string.
     *
     * @param  string  $value  The value to escape.
     */
    public static function escapeSingleQuotes(string $value): string
    {
        return str_replace('\'', '\\\'', $value);
    }

    /**
     * Normalizes line endings within the provided content.
     *
     * @param  string  $content  The content to normalize.
     */
    public static function normalizeLineEndings(string $content): string
    {
        return str_replace(["\r\n", "\r"], "\n", $content);
    }

    /**
     * Wraps the provided value in single quotes if it is not already.
     *
     * @param  string  $value  The string to wrap.
     */
    public static function wrapInSingleQuotes(string $value): string
    {
        if (Str::startsWith($value, '$')) {
            return $value;
        }

        if (Str::startsWith($value, "'") && Str::endsWith($value, "'")) {
            return $value;
        }

        return "'".$value."'";
    }

    /**
     * Removes balanced parentheses from the provided string.
     *
     * @param  string  $value  The value to unwrap.
     */
    public static function unwrapParentheses(string $value): string
    {
        while (Str::startsWith($value, '(') && Str::endsWith($value, ')')) {
            $value = mb_substr($value, 1, -1);
        }

        return $value;
    }

    public static function unwrapString(string $value): string
    {
        if (Str::startsWith($value, '"') && Str::endsWith($value, '"')) {
            return mb_substr($value, 1, -1);
        }

        if (Str::startsWith($value, "'") && Str::endsWith($value, "'")) {
            return mb_substr($value, 1, -1);
        }

        return $value;
    }

    /**
     * Tests if the provided value has leading whitespace.
     *
     * @param  string  $value  The value.
     */
    public static function hasLeadingWhitespace(string $value): bool
    {
        if (mb_strlen($value) == 0) {
            return false;
        }

        return ctype_space(mb_substr($value, 1));
    }

    /**
     * Tests if the provided value has trailing whitespace.
     *
     * @param  string  $value  The value.
     */
    public static function hasTrailingWhitespace(string $value): bool
    {
        if (mb_strlen($value) == 0) {
            return false;
        }

        return ctype_space(mb_substr($value, -1));
    }

    /**
     * Ensures the value has leading and trailing whitespace.
     *
     * A single space will be added to either the start or end.
     *
     * @param  string  $value  The value.
     */
    public static function ensureStringHasWhitespace(string $value): string
    {
        if (! self::hasLeadingWhitespace($value)) {
            $value = ' '.$value;
        }

        if (! self::hasTrailingWhitespace($value)) {
            $value = $value.' ';
        }

        return $value;
    }

    /**
     * Retrieves the leading whitespace from the provided value.
     *
     * @param  string  $value  The value.
     */
    public static function extractLeadingWhitespace(string $value): string
    {
        if (mb_strlen(trim($value)) == 0) {
            return '';
        }

        $chars = [];
        foreach (mb_str_split($value) as $char) {
            $chars[] = $char;

            if (! ctype_space($char) && $char != AbstractParser::C_NewLine) {
                array_pop($chars);
                break;
            }
        }

        return implode('', $chars);
    }

    /**
     * Retrieves the trailing whitespace from the provided value.
     *
     * @param  string  $value  The value.
     */
    public static function extractTrailingWhitespace(string $value): string
    {
        return Str::reverse(self::extractLeadingWhitespace(Str::reverse($value)));
    }

    /**
     * Converts a string into an array of strings, by new line character.
     *
     * @param  string  $input  The content to analyze.
     * @return string[]
     */
    public static function breakByNewLine(string $input): array
    {
        return explode("\n", $input);
    }
}
