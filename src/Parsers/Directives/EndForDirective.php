<?php

namespace Stillat\BladeParser\Parsers\Directives;

class EndForDirective extends LanguageDirective
{
    public $name = 'endfor';

    public $isTagPair = true;
    public $isStructure = true;
}
