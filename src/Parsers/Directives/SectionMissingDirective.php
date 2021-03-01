<?php

namespace Stillat\BladeParser\Parsers\Directives;

class SectionMissingDirective extends LanguageDirective
{

    public $name = 'sectionMissing';

    public $isClosedBy = 'endif';
    public $isStructure = false;
    public $isTagPair = true;

}
