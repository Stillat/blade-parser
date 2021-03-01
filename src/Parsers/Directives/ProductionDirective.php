<?php

namespace Stillat\BladeParser\Parsers\Directives;

class ProductionDirective extends LanguageDirective
{

    public $name = 'production';

    public $isClosedBy = 'endproduction';
    public $isStructure = false;
    public $isTagPair = true;

}
