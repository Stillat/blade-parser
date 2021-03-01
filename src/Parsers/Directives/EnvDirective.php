<?php

namespace Stillat\BladeParser\Parsers\Directives;

class EnvDirective extends LanguageDirective
{
    public $name = 'env';

    public $isClosedBy = 'endenv';
    public $isStructure = false;
    public $isTagPair = true;
}
