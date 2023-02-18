<?php

namespace Stillat\BladeParser\Compiler\Attributes;

enum ArgumentRequirement
{
    case Required;
    case NoArguments;
    case Optional;
}
