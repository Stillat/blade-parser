<?php

namespace Stillat\BladeParser\Parsers\Concerns;

trait  HandlesInputReplacements
{

    protected $replacements = [];

    public function addReplacement(callable $callback)
    {
        $this->replacements[] = $callback;
    }

    protected function doReplacements($input)
    {
        foreach ($this->replacements as $replacement) {
            $input = $replacement($input);
        }

        return $input;
    }

}
