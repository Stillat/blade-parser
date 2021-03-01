<?php

namespace Stillat\BladeParser\Parsers\Directives;

class LangDirective extends LanguageDirective
{
    public $name = 'lang';
    public $isTagPair = true;
    public $isClosedBy = 'endlang';
}