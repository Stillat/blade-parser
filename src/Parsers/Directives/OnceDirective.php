<?php

namespace Stillat\BladeParser\Parsers\Directives;

class OnceDirective extends LanguageDirective
{
    public $name = 'once';

    public $isClosedBy = 'endonce';
    public $isStructure = false;
    public $isTagPair = true;
}
