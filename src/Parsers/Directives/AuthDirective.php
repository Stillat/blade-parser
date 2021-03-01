<?php

namespace Stillat\BladeParser\Parsers\Directives;

class AuthDirective extends LanguageDirective
{

    public $name = 'auth';

    public $isClosedBy = 'endauth';
    public $isStructure = false;
    public $isTagPair = true;

}
