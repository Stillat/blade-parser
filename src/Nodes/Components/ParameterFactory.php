<?php

namespace Stillat\BladeParser\Nodes\Components;

use Stillat\BladeParser\Parser\ComponentParser;

class ParameterFactory
{
    /**
     * Parses parameters within the provided content.
     *
     * @param  string  $parameterContent  The parameter content.
     */
    public static function fromText(string $parameterContent): array
    {
        $componentParser = new ComponentParser;

        return $componentParser->parseOnlyParameters($parameterContent);
    }

    /**
     * Parses a single parameter from the provided content.
     *
     * @param  string  $parameterContent  The parameter content.
     */
    public static function parameterFromText(string $parameterContent): ?ParameterNode
    {
        $params = self::fromText($parameterContent);

        if (count($params) == 0) {
            return null;
        }

        return $params[0];
    }
}
