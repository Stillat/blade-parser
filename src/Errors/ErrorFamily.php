<?php

namespace Stillat\BladeParser\Errors;

enum ErrorFamily
{
    case Parser;
    case Validation;
    case Compiler;

    public function label(): string
    {
        return match ($this) {
            self::Parser => 'P',
            self::Validation => 'V',
            self::Compiler => 'C',
        };
    }
}
