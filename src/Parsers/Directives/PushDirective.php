<?php

namespace Stillat\BladeParser\Parsers\Directives;

class PushDirective extends LanguageDirective
{
    public $name = 'push';
    public $isTagPair = true;
    public $isClosedBy = 'endpush';
}
