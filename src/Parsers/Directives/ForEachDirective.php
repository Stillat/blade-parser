<?php

namespace Stillat\BladeParser\Parsers\Directives;

class ForEachDirective extends LanguageDirective
{
    public $name = 'foreach';

    public $isClosedBy = 'endforeach';
    public $isTagPair = true;
    public $isStructure = true;
}
