<?php

namespace Stillat\BladeParser\Printers\Php\Concerns;

use Stillat\BladeParser\Nodes\Node;

trait PrintsInjections
{

    protected function print_inject(Node $node)
    {
        $segments = explode(',', preg_replace("/[\(\)]/", '', $node->innerContent()));

        $variable = trim($segments[0], " '\"");

        $service = trim($segments[1]);

        return "<?php \${$variable} = app({$service}); ?>";
    }

}