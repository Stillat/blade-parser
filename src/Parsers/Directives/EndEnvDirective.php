<?php

namespace Stillat\BladeParser\Parsers\Directives;

class EndEnvDirective extends LanguageDirective
{

    public $name = 'endenv';

    public $isStructure = false;
    public $isTagPair = true;

}
