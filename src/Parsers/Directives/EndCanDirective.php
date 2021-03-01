<?php

namespace Stillat\BladeParser\Parsers\Directives;

class EndCanDirective extends LanguageDirective
{
    public $name = 'endcan';

    public $isTagPair = true;
    public $isStructure = false;
}
