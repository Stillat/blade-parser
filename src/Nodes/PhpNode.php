<?php

namespace Stillat\BladeParser\Nodes;

class PhpNode extends StaticNode
{
    public $isSelfClosing = false;
    public $pairStart = null;
    public $pairEnd = null;
}