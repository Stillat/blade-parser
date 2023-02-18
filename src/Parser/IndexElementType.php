<?php

namespace Stillat\BladeParser\Parser;

enum IndexElementType
{
    case Directive;
    case PhpOpenTag;
    case PhpOpenTagWithEcho;
    case BladeEcho;
    case BladeEchoThree;
    case BladeRawEcho;
    case BladeComment;
    case ComponentOpenTag;
    case ComponentClosingTag;
    case CustomComponentOpenTag;
    case CustomComponentClosingTag;
}
