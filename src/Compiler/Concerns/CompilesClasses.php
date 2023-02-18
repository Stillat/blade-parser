<?php

namespace Stillat\BladeParser\Compiler\Concerns;

use Stillat\BladeParser\Compiler\Attributes\ArgumentRequirement;
use Stillat\BladeParser\Compiler\Attributes\CompilesDirective;
use Stillat\BladeParser\Compiler\Attributes\StructureType;
use Stillat\BladeParser\Nodes\DirectiveNode;

trait CompilesClasses
{
    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compileClass(DirectiveNode $node)
    {
        $expression = $node->hasArguments() ? $node->arguments->content : '([])';

        return "class=\"<?php echo \Illuminate\Support\Arr::toCssClasses{$expression} ?>\"";
    }
}
