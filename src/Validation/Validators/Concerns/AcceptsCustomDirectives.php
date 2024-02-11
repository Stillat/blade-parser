<?php

namespace Stillat\BladeParser\Validation\Validators\Concerns;

trait AcceptsCustomDirectives
{
    /**
     * @var string[]
     */
    protected array $customDirectives = [];

    /**
     * Sets the custom directive names.
     *
     * @param  string[]  $directives  The directive names.
     */
    public function setCustomDirectives(array $directives): void
    {
        $this->customDirectives = $directives;
    }
}
