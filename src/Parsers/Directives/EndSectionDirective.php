<?php

namespace Stillat\BladeParser\Parsers\Directives;

class EndSectionDirective extends LanguageDirective
{

    public $name = 'endsection';

    public $isTagPair = true;
    public $isStructure = true;

}
