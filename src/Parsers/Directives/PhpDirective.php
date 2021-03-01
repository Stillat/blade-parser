<?php

namespace Stillat\BladeParser\Parsers\Directives;

class PhpDirective extends LanguageDirective
{
    public $name = 'php';

    public $isClosedBy = 'endphp';
    public $isStructure = false;
    public $isTagPair = true;
}
