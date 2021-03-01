<?php

namespace Stillat\BladeParser\Parsers\Concerns;

use InvalidArgumentException;
use Stillat\BladeParser\Validators\NameValidators;

trait HandlesCustomDirectives
{
    protected $customDirectives = [];

    public function directive($name, callable $handler)
    {
        if (! NameValidators::isDirectiveNameValid($name)) {
            throw new InvalidArgumentException("The directive name [{$name}] is not valid. Directive names must only contain alphanumeric characters and underscores.");
        }

        $this->customDirectives[$name] = $handler;
    }

    public function getCustomDirectives()
    {
        return $this->customDirectives;
    }
}
