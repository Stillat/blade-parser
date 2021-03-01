<?php

namespace Stillat\BladeParser\Parsers\Directives;

class SectionDirective extends LanguageDirective
{

    public $name = 'section';
    public $isClosedBy = 'endsection';
    public $isTagPair = true;
    public $isStructure = false;

}