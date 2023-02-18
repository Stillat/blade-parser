<?php

namespace Stillat\BladeParser\Compiler\Concerns;

use Stillat\BladeParser\Compiler\Attributes\ArgumentRequirement;
use Stillat\BladeParser\Compiler\Attributes\CompilesDirective;
use Stillat\BladeParser\Compiler\Attributes\StructureType;
use Stillat\BladeParser\Nodes\DirectiveNode;

trait CompilesIncludes
{
    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compileEach(DirectiveNode $node): string
    {
        return "<?php echo \$__env->renderEach{$this->getDirectiveArgs($node)}; ?>";
    }

    #[CompilesDirective(StructureType::Include, ArgumentRequirement::Required)]
    protected function compileInclude(DirectiveNode $node): string
    {
        return "<?php echo \$__env->make({$this->getDirectiveArgsInnerContent($node)}, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>";
    }

    #[CompilesDirective(StructureType::Include, ArgumentRequirement::Required)]
    protected function compileIncludeIf(DirectiveNode $node): string
    {
        $expression = $this->getDirectiveArgsInnerContent($node);

        return "<?php if (\$__env->exists({$expression})) echo \$__env->make({$expression}, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>";
    }

    #[CompilesDirective(StructureType::Include, ArgumentRequirement::Required)]
    protected function compileIncludeWhen(DirectiveNode $node): string
    {
        $expression = $this->getDirectiveArgsInnerContent($node);

        return "<?php echo \$__env->renderWhen($expression, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path'])); ?>";
    }

    #[CompilesDirective(StructureType::Include, ArgumentRequirement::Required)]
    protected function compileIncludeUnless(DirectiveNode $node): string
    {
        $expression = $this->getDirectiveArgsInnerContent($node);

        return "<?php echo \$__env->renderUnless($expression, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path'])); ?>";
    }

    #[CompilesDirective(StructureType::Include, ArgumentRequirement::Required)]
    protected function compileIncludeFirst(DirectiveNode $node): string
    {
        $expression = $this->getDirectiveArgsInnerContent($node);

        return "<?php echo \$__env->first({$expression}, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>";
    }
}
