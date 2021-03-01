<?php

namespace Stillat\BladeParser\Printers\Php\Concerns;

use Stillat\BladeParser\Nodes\Node;

trait PrintsExtends
{

    protected function print_extends(Node $node)
    {
        return '<?php echo $__env->make('.$node->innerContent().', \Illuminate\Support\Arr::except(get_defined_vars(), [\'__data\', \'__path\']))->render(); ?>';
    }

}