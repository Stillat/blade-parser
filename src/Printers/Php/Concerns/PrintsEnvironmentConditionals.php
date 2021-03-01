<?php

namespace Stillat\BladeParser\Printers\Php\Concerns;

use Stillat\BladeParser\Nodes\Node;

trait PrintsEnvironmentConditionals
{

    protected function print_env(Node $node)
    {
        return '<?php if(app()->environment('.$node->innerContent().')): ?>';
    }

    protected function print_endenv(Node $node)
    {
        return $this->phpEndIf();
    }

    protected function print_production(Node $node)
    {
        return "<?php if(app()->environment('production')): ?>";

    }

    protected function print_endproduction(Node $node)
    {
        return $this->phpEndIf();
    }

}