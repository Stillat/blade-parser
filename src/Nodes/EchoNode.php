<?php

namespace Stillat\BladeParser\Nodes;

class EchoNode extends DirectiveNode
{

    public $isSafe = false;
    public $openCount = 2;

}
