<?php

namespace Stillat\BladeParser\Compiler\Concerns;

use Stillat\BladeParser\Compiler\Attributes\ArgumentRequirement;
use Stillat\BladeParser\Compiler\Attributes\CompilesDirective;
use Stillat\BladeParser\Compiler\Attributes\StructureType;
use Stillat\BladeParser\Nodes\DirectiveNode;

trait CompilesTranslations
{
    #[CompilesDirective(StructureType::Mixed, ArgumentRequirement::Optional)]
    protected function compileLang(DirectiveNode $node): string
    {
        if (! $node->hasArguments()) {
            return '<?php $__env->startTranslation(); ?>';
        }

        $expression = $this->getDirectiveArgs($node);

        if ($expression[1] === '[') {
            return "<?php \$__env->startTranslation{$expression}; ?>";
        }

        return "<?php echo app('translator')->get{$expression}; ?>";
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    protected function compileEndlang(): string
    {
        return '<?php echo $__env->renderTranslation(); ?>';
    }

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compileChoice(DirectiveNode $node): string
    {
        return "<?php echo app('translator')->choice{$this->getDirectiveArgs($node)}; ?>";
    }
}
