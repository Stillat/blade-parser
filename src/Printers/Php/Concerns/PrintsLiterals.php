<?php

namespace Stillat\BladeParser\Printers\Php\Concerns;

use Stillat\BladeParser\Nodes\Node;

trait PrintsLiterals
{
    protected function print_literal(Node $node)
    {
        return $node->innerContent;
    }
}
