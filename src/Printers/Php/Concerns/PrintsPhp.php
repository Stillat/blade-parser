<?php

namespace Stillat\BladeParser\Printers\Php\Concerns;

use Stillat\BladeParser\Nodes\Node;
use Stillat\BladeParser\Nodes\PhpNode;

trait PrintsPhp
{

    protected function print_php(Node $node)
    {
        if ($node instanceof PhpNode) {
            if ($node->isSelfClosing == true) {
                return '<?php (' . $node->innerContent() . '); ?>';
            }

            return '<?php' . $node->innerContent() . '?>';
        }
    }

    protected function print_endphp(Node $node)
    {
        return '';
    }

    protected function print_unset(Node $node)
    {
        return '<?php unset('.$node->innerContent().'); ?>';
    }

}