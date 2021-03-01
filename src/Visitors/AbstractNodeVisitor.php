<?php

namespace Stillat\BladeParser\Visitors;

use Stillat\BladeParser\Nodes\Node;

abstract class AbstractNodeVisitor
{
    abstract public function onEnter(Node $node);
}
