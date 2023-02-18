<?php

namespace Stillat\BladeParser\Nodes\Fragments;

enum FragmentPosition
{
    case InsideFragment;
    case InsideAttribute;
    case InsideParameter;
    case InsideFragmentName;
    case StartOfFragment;
    case EndOfFragment;
    case Unknown;
}
