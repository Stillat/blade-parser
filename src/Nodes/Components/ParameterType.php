<?php

namespace Stillat\BladeParser\Nodes\Components;

enum ParameterType
{
    case Parameter;
    case Attribute;
    case DynamicVariable;
    case ShorthandDynamicVariable;
    case InterpolatedValue;
    case EscapedParameter;
    case AttributeEcho;
    case AttributeRawEcho;
    case AttributeTripleEcho;
    case UnknownEcho;
    case UnknownRawEcho;
    case UnknownTripleEcho;
}
