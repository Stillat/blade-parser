<?php

namespace Stillat\BladeParser\Parser;

class BladeKeywords
{
    public const K_Verbatim = 'verbatim';

    public const K_EndVerbatim = 'endverbatim';

    public const K_Php = 'php';

    public const K_EndPhp = 'endphp';

    public const K_If = 'if';

    public const K_ElseIf = 'elseif';

    public const K_ELSE = 'else';

    public const K_EndIf = 'endif';

    public const K_Unless = 'unless';

    public const K_ForElse = 'forelse';

    public const K_EndForElse = 'endforelse';

    public const K_Switch = 'switch';

    public const K_Empty = 'empty';

    public const K_EndSwitch = 'endswitch';

    public const K_Default = 'default';

    public const K_Case = 'case';

    public const K_Break = 'break';

    public const K_As = 'as';
}
