<?php

namespace Stillat\BladeParser\Compiler\Attributes;

enum StructureType
{
    case Open;
    case Terminator;
    case Mixed;
    case EchoHelper;
    case Debug;
    case Extension;
    case Include;
}
