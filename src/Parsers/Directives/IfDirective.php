<?php

namespace Stillat\BladeParser\Parsers\Directives;

class IfDirective extends LanguageDirective
{
    public $name = 'if';
    public $isClosedBy = 'endif';
    public $isStructure = true;
    public $isTagPair = true;
}
