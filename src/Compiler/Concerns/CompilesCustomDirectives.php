<?php

namespace Stillat\BladeParser\Compiler\Concerns;

trait CompilesCustomDirectives
{
    protected function callCustomDirective(string $name, string $value): string
    {
        return call_user_func($this->customDirectives[$name], trim($value));
    }
}
