<?php

namespace Stillat\BladeParser\Printers\Php\Concerns;

use Stillat\BladeParser\Nodes\Node;

trait PrintsHelpers
{
    protected function print_csrf(Node $node)
    {
        return '<?php echo csrf_field(); ?>';
    }

    protected function print_method(Node $node)
    {
        return '<?php echo method_field('.$node->innerContent().'); ?>';
    }

    protected function print_dd(Node $node)
    {
        return '<?php dd('.$node->innerContent().'); ?>';
    }

    protected function print_dump(Node $node)
    {
        return '<?php dump('.$node->innerContent().'); ?>';
    }
}
