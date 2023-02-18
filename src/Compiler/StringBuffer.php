<?php

namespace Stillat\BladeParser\Compiler;

class StringBuffer
{
    protected string $value = '';

    public function append(string $content): StringBuffer
    {
        $this->value .= $content;

        return $this;
    }

    public function clear(): StringBuffer
    {
        $this->value = '';

        return $this;
    }

    public function currentLine(): int
    {
        return mb_substr_count($this->value, "\n") + 1;
    }

    public function toString(): string
    {
        return (string) $this;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
