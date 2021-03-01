<?php

namespace Stillat\BladeParser\Parsers\Directives;

class HasSectionDirective extends LanguageDirective
{

    public $name = 'hasSection';

    public $isClosedBy = 'endif';
    public $isStructure = false;
    public $isTagPair = true;

}
