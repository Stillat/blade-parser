<?php

namespace Stillat\BladeParser\Printers\Php\Concerns;

use Stillat\BladeParser\Nodes\Node;

trait PrintsVerbatim
{

    protected function print_verbatim(Node $node)
    {
        return $node->innerContent();
    }

    protected function print_endverbatim(Node $node)
    {
        return '';
    }

}