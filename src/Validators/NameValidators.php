<?php

namespace Stillat\BladeParser\Validators;

class NameValidators
{

    public static function isDirectiveNameValid($name)
    {
        if (!preg_match('/^\w+(?:::\w+)?$/x', $name)) {
            return false;
        }

        return true;
    }

}
