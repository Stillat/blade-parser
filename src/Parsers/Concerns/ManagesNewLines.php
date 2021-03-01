<?php

namespace Stillat\BladeParser\Parsers\Concerns;

trait ManagesNewLines
{

    private function detectNewLine($content)
    {
        $arr = array_count_values(
            explode(
                ' ',
                preg_replace(
                    '/[^\r\n]*(\r\n|\n|\r)/',
                    '\1 ',
                    $content
                )
            )
        );
        arsort($arr);

        return key($arr);
    }

    private function removeNewLines($string)
    {
        return trim(str_replace(array("\r\n","\r"), '',$string));
    }

    private function normalizeLineEndings($string, $to = "\n")
    {
        return preg_replace("/\r\n|\r|\n/", $to, $string);
    }

}