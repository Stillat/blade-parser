<?php

namespace Stillat\BladeParser\Printers\Php\Concerns;

use Stillat\BladeParser\Nodes\Node;

trait PrintsAppend
{

    protected function print_append(Node $node)
    {
        return '<?php $__env->appendSection(); ?>';
    }
    
}
