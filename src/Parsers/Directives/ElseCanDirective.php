<?php

namespace Stillat\BladeParser\Parsers\Directives;

class ElseCanDirective extends LanguageDirective
{
    public $name = 'elsecan';

    public $mustAppearIn = ['can'];
}
