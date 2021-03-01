<?php

namespace Stillat\BladeParser\Printers\Php\Concerns;

use Stillat\BladeParser\Nodes\Node;

trait PrintsStacks
{
    protected function print_stack(Node $node)
    {
        return '<?php echo $__env->yieldPushContent('.$node->innerContent().'); ?>';
    }

    protected function print_prepend(Node $node)
    {
        return '<?php $__env->startPrepend('.$node->innerContent().'); ?>';
    }

    protected function print_stop(Node $node)
    {
        return '<?php $__env->stopSection(); ?>';
    }

    protected function print_endprepend(Node $node)
    {
        return '<?php $__env->stopPrepend(); ?>';
    }

    protected function print_push(Node $node)
    {
        return '<?php $__env->startPush('.$node->innerContent().'); ?>';
    }

    protected function print_endpush(Node $node)
    {
        return '<?php $__env->stopPush(); ?>';
    }
}
