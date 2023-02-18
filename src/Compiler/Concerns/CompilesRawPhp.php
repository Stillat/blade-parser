<?php

namespace Stillat\BladeParser\Compiler\Concerns;

use Stillat\BladeParser\Compiler\Attributes\ArgumentRequirement;
use Stillat\BladeParser\Compiler\Attributes\CompilesDirective;
use Stillat\BladeParser\Compiler\Attributes\StructureType;
use Stillat\BladeParser\Nodes\DirectiveNode;

trait CompilesRawPhp
{
    #[CompilesDirective(StructureType::Mixed, ArgumentRequirement::Required)]
    protected function compileUnset(DirectiveNode $node): string
    {
        return "<?php unset{$this->getDirectiveArgs($node)}; ?>";
    }

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Optional)]
    protected function compilePhp(DirectiveNode $node): string
    {
        if ($node->hasArguments()) {
            return '<?php '.$this->getDirectiveArgs($node).'; ?>';
        }

        return '@php';
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    protected function compileEndphp(DirectiveNode $node)
    {
        return '';
    }
}
