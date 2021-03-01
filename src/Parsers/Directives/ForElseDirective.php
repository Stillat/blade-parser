<?php

namespace Stillat\BladeParser\Parsers\Directives;

class ForElseDirective extends LanguageDirective
{

    public $name = 'forelse';

    public $isClosedBy = 'endforelse';
    public $isTagPair = true;
    public $isStructure = true;

}
