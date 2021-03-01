<?php

namespace Stillat\BladeParser\Parsers\Directives;

class EndForEachDirective extends LanguageDirective
{

    public $name = 'endforeach';

    public $isTagPair = true;
    public $isStructure = true;

}
