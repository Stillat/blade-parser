<?php

namespace Stillat\BladeParser\Parsers\Directives;

class EndEmptyDirective extends LanguageDirective
{
    public $name = 'endempty';

    public $isStructure = false;
    public $isTagPair = true;
}
