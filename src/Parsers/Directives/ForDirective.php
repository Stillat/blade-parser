<?php

namespace Stillat\BladeParser\Parsers\Directives;

class ForDirective extends LanguageDirective
{

    public $name = 'for';

    public $isClosedBy = 'endfor';

    public $isTagPair = true;
    public $isStructure = true;

}
