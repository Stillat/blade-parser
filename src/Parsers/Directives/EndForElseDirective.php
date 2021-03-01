<?php

namespace Stillat\BladeParser\Parsers\Directives;

class EndForElseDirective extends LanguageDirective
{

    public $name = 'endforelse';

    public $isTagPair = true;
    public $isStructure = true;

}
