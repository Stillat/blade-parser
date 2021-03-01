<?php

namespace Stillat\BladeParser\Parsers\Directives;

class CanDirective extends LanguageDirective
{
    public $name = 'can';

    public $isClosedBy = 'endcan';
    public $isTagPair = true;
    public $isStructure = false;
}
