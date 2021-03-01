<?php

namespace Stillat\BladeParser\Parsers\Directives;

class ElseCanAnyDirective extends LanguageDirective
{
    public $name = 'elsecanany';
    public $mustAppearIn = ['canany'];

}