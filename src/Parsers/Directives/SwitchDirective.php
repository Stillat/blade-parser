<?php

namespace Stillat\BladeParser\Parsers\Directives;

class SwitchDirective extends LanguageDirective
{
    public $name = 'switch';

    public $isClosedBy = 'endswitch';
    public $isStructure = true;
    public $isTagPair = true;
}
