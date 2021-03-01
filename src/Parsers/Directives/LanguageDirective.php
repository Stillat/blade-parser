<?php

namespace Stillat\BladeParser\Parsers\Directives;

abstract class LanguageDirective
{

    public $name = '';

    public $mustAppearIn = [];
    public $canAppearIn = [];

    public $isStructure = false;
    public $isTagPair = false;
    public $isClosedBy = null;

    /**
     * Indicates if this directive must be enclosed.
     *
     * @return bool
     */
    public function mustBeEnclosed()
    {
        if ($this->mustAppearIn === null) {
            return false;
        }

        return count($this->mustAppearIn) > 0;
    }

}

