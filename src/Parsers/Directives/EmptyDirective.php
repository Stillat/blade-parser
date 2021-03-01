<?php

namespace Stillat\BladeParser\Parsers\Directives;

class EmptyDirective extends LanguageDirective
{

    public $name = 'empty';

    public $isClosedBy = [ 'endempty'];
    public $canAppearIn = ['forelse'];

    public $isStructure = false;
    public $isTagPair = true;

}
