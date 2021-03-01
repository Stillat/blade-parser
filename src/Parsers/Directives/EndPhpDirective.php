<?php

namespace Stillat\BladeParser\Parsers\Directives;

class EndPhpDirective extends LanguageDirective
{
    public $name = 'endphp';

    public $isStructure = false;
    public $isTagPair = true;
}
