<?php

namespace Stillat\BladeParser\Printers\Php\Concerns;

use Stillat\BladeParser\Nodes\Node;

trait PrintsSectionsAndLayout
{
    protected function print_show(Node $node)
    {
        return '<?php echo $__env->yieldSection(); ?>';
    }

    protected function print_section(Node $node)
    {
        return '<?php $__env->startSection('.$node->innerContent().'); ?>';
    }

    protected function print_sectionmissing(Node $node)
    {
        return '<?php if (empty(trim($__env->yieldContent('.$node->innerContent().')))): ?>';
    }

    protected function print_endsection(Node $node)
    {
        return '<?php $__env->stopSection(); ?>';
    }

    protected function print_hassection(Node $node)
    {
        return '<?php if (! empty(trim($__env->yieldContent("'.$this->trimQuotes($node->innerContent()).'")))): ?>';
    }

    protected function print_overwrite(Node $node)
    {
        return '<?php $__env->stopSection(true); ?>';
    }

    protected function print_yield(Node $node)
    {
        return '<?php echo $__env->yieldContent('.$node->innerContent().'); ?>';
    }
}
