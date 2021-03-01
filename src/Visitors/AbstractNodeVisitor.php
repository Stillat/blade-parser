<?php

namespace Stillat\BladeParser\Visitors;

use Stillat\BladeParser\Nodes\Node;

abstract class AbstractNodeVisitor
{

    public abstract function onEnter(Node $node);

}
