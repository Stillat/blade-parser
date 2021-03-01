<?php

namespace Stillat\BladeParser\Printers\Php\Concerns;

use Stillat\BladeParser\Nodes\Node;

trait PrintsIncludes
{

    protected function print_each(Node $node)
    {
        return '<?php echo $__env->renderEach(' . $node->innerContent() . '); ?>';
    }

    protected function print_include(Node $node)
    {
        $expression = $node->innerContent();

        return "<?php echo \$__env->make({$expression}, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>";
    }

    protected function print_includeif(Node $node)
    {
        $expression = $node->innerContent();

        return "<?php if (\$__env->exists({$expression})) echo \$__env->make({$expression}, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>";
    }

    protected function print_includewhen(Node $node)
    {
        $expression = $node->innerContent();

        return "<?php echo \$__env->renderWhen($expression, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path'])); ?>";
    }

    protected function print_includeunless(Node $node)
    {
        $expression = $node->innerContent();

        return "<?php echo \$__env->renderWhen(! $expression, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path'])); ?>";
    }

    protected function print_includefirst(Node $node)
    {
        $expression = $node->innerContent();

        return "<?php echo \$__env->first({$expression}, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>";
    }

}