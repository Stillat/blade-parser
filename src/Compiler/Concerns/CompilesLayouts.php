<?php

namespace Stillat\BladeParser\Compiler\Concerns;

use Stillat\BladeParser\Compiler\Attributes\ArgumentRequirement;
use Stillat\BladeParser\Compiler\Attributes\CompilesDirective;
use Stillat\BladeParser\Compiler\Attributes\StructureType;
use Stillat\BladeParser\Nodes\DirectiveNode;

trait CompilesLayouts
{
    protected string $lastSection = '';

    #[CompilesDirective(StructureType::Extension, ArgumentRequirement::Required)]
    protected function compileExtends(DirectiveNode $node): string
    {
        $expression = $this->getDirectiveArgsInnerContent($node);

        $echo = "<?php echo \$__env->make({$expression}, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>";

        $this->footer[] = $echo;

        return '';
    }

    #[CompilesDirective(StructureType::Extension, ArgumentRequirement::Required)]
    protected function compileExtendsFirst(DirectiveNode $node): string
    {
        $expression = $this->getDirectiveArgsInnerContent($node);

        $echo = "<?php echo \$__env->first({$expression}, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>";

        $this->footer[] = $echo;

        return '';
    }

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compileSection(DirectiveNode $node): string
    {
        $expression = $this->getDirectiveArgsInnerContent($node);

        $this->lastSection = trim($expression, "()'\" ");

        return "<?php \$__env->startSection{$this->getDirectiveArgs($node)}; ?>";
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::Required)]
    protected function compileParent(): string
    {
        $escapedLastSection = strtr($this->lastSection, ['\\' => '\\\\', "'" => "\\'"]);

        return "<?php echo \Illuminate\View\Factory::parentPlaceholder('{$escapedLastSection}'); ?>";
    }

    #[CompilesDirective(StructureType::EchoHelper, ArgumentRequirement::Required)]
    protected function compileYield(DirectiveNode $node): string
    {
        return "<?php echo \$__env->yieldContent{$this->getDirectiveArgs($node)}; ?>";
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    protected function compileShow(): string
    {
        return '<?php echo $__env->yieldSection(); ?>';
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    protected function compileAppend(): string
    {
        return '<?php $__env->appendSection(); ?>';
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    protected function compileOverwrite(): string
    {
        return '<?php $__env->stopSection(true); ?>';
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    protected function compileStop(): string
    {
        return '<?php $__env->stopSection(); ?>';
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    protected function compileEndsection(): string
    {
        return '<?php $__env->stopSection(); ?>';
    }
}
