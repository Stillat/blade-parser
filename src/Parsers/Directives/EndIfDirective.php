<?php

namespace Stillat\BladeParser\Parsers\Directives;

class EndIfDirective extends LanguageDirective
{

    public $name = 'endif';

    public $isTagPair = true;
    public $isStructure = true;

}
