<?php

namespace Stillat\BladeParser\Compiler\Concerns;

use Stillat\BladeParser\Compiler\Attributes\ArgumentRequirement;
use Stillat\BladeParser\Compiler\Attributes\CompilesDirective;
use Stillat\BladeParser\Compiler\Attributes\StructureType;
use Stillat\BladeParser\Nodes\DirectiveNode;

trait CompilesFragments
{
    protected string $lastFragment = '';

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compileFragment(DirectiveNode $node): string
    {
        $this->lastFragment = trim($this->getDirectiveArgsInnerContent($node), "()'\" ");

        return "<?php \$__env->startFragment{$this->getDirectiveArgs($node)}; ?>";
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    protected function compileEndfragment(): string
    {
        return '<?php echo $__env->stopFragment(); ?>';
    }
}
