<?php

namespace Stillat\BladeParser\Parsers\Directives;

class ErrorDirective extends LanguageDirective
{
    public $name = 'error';
    public $isTagPair = true;
    public $isClosedBy = 'enderror';
}