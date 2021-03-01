<?php

namespace Stillat\BladeParser\Printers\Php\Concerns;

use Stillat\BladeParser\Nodes\EchoNode;
use Stillat\BladeParser\Nodes\Node;

trait PrintsEchos
{

    protected function print_echo(Node $node)
    {
        if ($node instanceof EchoNode) {
            if ($node->isSafe) {
                return '<?php echo e('.$this->removeNewLines($node->innerContent()).'); ?>';
            } else {
                return '<?php echo '.$this->removeNewLines($node->innerContent()).'; ?>';
            }
        }
    }

}