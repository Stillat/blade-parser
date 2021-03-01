<?php

namespace Stillat\BladeParser\Printers\Generators;

class StructureGenerator
{
    protected $buffer = '';

    public static function make()
    {
        return new StructureGenerator();
    }

    public function nl()
    {
        return $this->literal();
    }

    public function literal()
    {
        $this->buffer .= '{node:literal}';

        return $this;
    }

    public function directive($name)
    {
        $this->buffer .= '{node:'.$name.'}';

        return $this;
    }

    public function contents()
    {
        return $this->buffer;
    }
}
