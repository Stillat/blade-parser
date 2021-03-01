<?php

namespace Stillat\BladeParser\Parsers\Directives;

class ElseDirective extends LanguageDirective
{
    public $name = 'else';
    public $isStructure = true;
    public $isTagPair = true;
}
