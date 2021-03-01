<?php

namespace Stillat\BladeParser\Parsers\Directives;

class UnlessDirective extends LanguageDirective
{
    public $name = 'unless';

    public $isClosedBy = 'endunless';
    public $isStructure = false;
    public $isTagPair = true;
}
