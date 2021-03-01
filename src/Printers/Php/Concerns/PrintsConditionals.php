<?php

namespace Stillat\BladeParser\Printers\Php\Concerns;

use Stillat\BladeParser\Nodes\Node;

trait PrintsConditionals
{
    protected function print_switch(Node $node)
    {
        return '<?php switch('.$node->innerContent().'):';
    }

    protected function print_endswitch(Node $node)
    {
        return '<?php endswitch; ?>';
    }

    protected function print_case(Node $node)
    {
        if ($node->isFirstOfType()) {
            return 'case ('.$node->innerContent().'): ?>';
        }

        return '<?php case ('.$node->innerContent().'): ?>';
    }

    protected function print_if(Node $node)
    {
        return '<?php if('.$node->innerContent().'): ?>';
    }

    protected function print_elseif(Node $node)
    {
        return '<?php elseif('.$node->innerContent().'): ?>';
    }

    protected function print_else(Node $node)
    {
        return '<?php else: ?>';
    }

    protected function print_endif(Node $node)
    {
        return $this->phpEndIf();
    }

    public function print_isset(Node $node)
    {
        return '<?php if(isset('.$node->innerContent().')): ?>';
    }

    protected function print_endisset(Node $node)
    {
        return $this->phpEndIf();
    }

    protected function print_unless(Node $node)
    {
        return '<?php if (! ('.$node->innerContent().')): ?>';
    }

    protected function print_endunless(Node $node)
    {
        return $this->phpEndIf();
    }
}
