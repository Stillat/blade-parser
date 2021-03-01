<?php

namespace Stillat\BladeParser\Parsers\Directives;

class PrependDirective extends LanguageDirective
{
    public $name = 'prepend';
    public $isClosedBy = 'endprepend';
    public $isTagPair = true;
}
