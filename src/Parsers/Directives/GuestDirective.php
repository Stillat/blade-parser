<?php

namespace Stillat\BladeParser\Parsers\Directives;

class GuestDirective extends LanguageDirective
{

    public $name = 'guest';

    public $isClosedBy = 'endguest';
    public $isStructure = false;
    public $isTagPair = true;

}
