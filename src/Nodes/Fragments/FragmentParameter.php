<?php

namespace Stillat\BladeParser\Nodes\Fragments;

use Illuminate\Support\Str;

class FragmentParameter extends Fragment
{
    /**
     * The parameter name.
     */
    public string $name = '';

    /**
     * The parameter value, if available.
     */
    public string $value = '';

    public FragmentParameterType $type = FragmentParameterType::Attribute;

    /**
     * Retrieves the parameter's value.
     *
     * If the value is enclosed in balanced strings, the string value will be unwrapped.
     */
    public function getValue(): string
    {
        if ((Str::startsWith($this->value, '"') && Str::endsWith($this->value, '"')) ||
            (Str::startsWith($this->value, "'") && Str::endsWith($this->value, "'"))) {
            return mb_substr($this->value, 1, -1);
        }

        return $this->value;
    }
}
