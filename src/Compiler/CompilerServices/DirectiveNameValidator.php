<?php

namespace Stillat\BladeParser\Compiler\CompilerServices;

class DirectiveNameValidator
{
    /**
     * Tests if the provided directive name is valid.
     *
     * @param  string  $name  The directive name.
     */
    public static function isNameValid(string $name): bool
    {
        if (! preg_match('/^\w+(?:::\w+)?$/x', $name)) {
            return false;
        }

        return true;
    }
}
