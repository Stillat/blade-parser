<?php

namespace Stillat\BladeParser\Parsers\Directives;

class IsSetDirective extends LanguageDirective
{
    public $name = 'isset';

    public $isClosedBy = 'endisset';
    public $isStructure = false;
    public $isTagPair = true;
}
