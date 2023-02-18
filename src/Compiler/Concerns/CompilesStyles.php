<?php

namespace Stillat\BladeParser\Compiler\Concerns;

use Stillat\BladeParser\Compiler\Attributes\ArgumentRequirement;
use Stillat\BladeParser\Compiler\Attributes\CompilesDirective;
use Stillat\BladeParser\Compiler\Attributes\StructureType;
use Stillat\BladeParser\Nodes\DirectiveNode;

trait CompilesStyles
{
    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compileStyles(DirectiveNode $node): string
    {
        $expression = $node->hasArguments() ? $node->arguments->content : '([])';

        return "style=\"<?php echo \Illuminate\Support\Arr::toCssStyles{$expression} ?>\"";
    }
}
