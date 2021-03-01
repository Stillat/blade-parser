<?php

namespace Stillat\BladeParser\Parsers\Directives;

class CanAnyDirective extends LanguageDirective
{
    public $name = 'canany';

    public $isTagPair = true;
    public $isClosedBy = 'endcan';
}
