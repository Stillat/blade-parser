<?php

namespace Stillat\BladeParser\Compiler\Concerns;

use Stillat\BladeParser\Compiler\Attributes\ArgumentRequirement;
use Stillat\BladeParser\Compiler\Attributes\CompilesDirective;
use Stillat\BladeParser\Compiler\Attributes\StructureType;
use Stillat\BladeParser\Nodes\DirectiveNode;

trait CompilesAuthorizations
{
    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compileCan(DirectiveNode $node): string
    {
        return "<?php if (app(\Illuminate\\Contracts\\Auth\\Access\\Gate::class)->check{$this->getDirectiveArgs($node)}): ?>";
    }

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compileCannot(DirectiveNode $node): string
    {
        return "<?php if (app(\Illuminate\\Contracts\\Auth\\Access\\Gate::class)->denies{$this->getDirectiveArgs($node)}): ?>";
    }

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compileCanany(DirectiveNode $node): string
    {
        return "<?php if (app(\Illuminate\\Contracts\\Auth\\Access\\Gate::class)->any{$this->getDirectiveArgs($node)}): ?>";
    }

    #[CompilesDirective(StructureType::Mixed, ArgumentRequirement::Required)]
    protected function compileElsecan(DirectiveNode $node): string
    {
        return "<?php elseif (app(\Illuminate\\Contracts\\Auth\\Access\\Gate::class)->check{$this->getDirectiveArgs($node)}): ?>";
    }

    #[CompilesDirective(StructureType::Mixed, ArgumentRequirement::Required)]
    protected function compileElsecannot(DirectiveNode $node): string
    {
        return "<?php elseif (app(\Illuminate\\Contracts\\Auth\\Access\\Gate::class)->denies{$this->getDirectiveArgs($node)}): ?>";
    }

    #[CompilesDirective(StructureType::Mixed, ArgumentRequirement::Required)]
    protected function compileElsecanany(DirectiveNode $node): string
    {
        return "<?php elseif (app(\Illuminate\\Contracts\\Auth\\Access\\Gate::class)->any{$this->getDirectiveArgs($node)}): ?>";
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    protected function compileEndcan(): string
    {
        return '<?php endif; ?>';
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    protected function compileEndcannot(): string
    {
        return '<?php endif; ?>';
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    protected function compileEndcanany(): string
    {
        return '<?php endif; ?>';
    }
}
