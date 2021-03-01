<?php

namespace Stillat\BladeParser\Parsers\Directives;

class CaseDirective extends LanguageDirective
{

    public $name = 'case';

    public $mustAppearIn = ['switch'];

}
