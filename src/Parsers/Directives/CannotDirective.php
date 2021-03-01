<?php

namespace Stillat\BladeParser\Parsers\Directives;

class CannotDirective extends LanguageDirective
{

    public $name = 'cannot';
    public $isClosedBy = 'endcannot';
    public $isTagPair = true;

}