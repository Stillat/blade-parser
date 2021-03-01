<?php

namespace Stillat\BladeParser\Parsers\Directives;

class WhileDirective extends LanguageDirective
{
    public $name = 'while';
    public $isTagPair = true;
    public $isClosedBy = 'endwhile';
}
