<?php

namespace Stillat\BladeParser\Parsers\Directives;

class ElseCannotDirective extends LanguageDirective
{
    public $name = 'elsecannot';
    public $mustAppearIn = ['cannot'];
}
