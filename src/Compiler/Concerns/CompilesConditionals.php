<?php

namespace Stillat\BladeParser\Compiler\Concerns;

use Illuminate\Support\Str;
use Stillat\BladeParser\Compiler\Attributes\ArgumentRequirement;
use Stillat\BladeParser\Compiler\Attributes\CompilesDirective;
use Stillat\BladeParser\Compiler\Attributes\StructureType;
use Stillat\BladeParser\Nodes\DirectiveNode;

trait CompilesConditionals
{
    protected bool $firstCaseInSwitch = true;

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compileAuth(DirectiveNode $node): string
    {
        return "<?php if(auth()->guard{$this->getDirectiveArgs($node)}->check()): ?>";
    }

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compileElseAuth(DirectiveNode $node): string
    {
        return "<?php elseif(auth()->guard{$this->getDirectiveArgs($node)}->check()): ?>";
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    protected function compileEndAuth(): string
    {
        return '<?php endif; ?>';
    }

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compileEnv(DirectiveNode $node): string
    {
        return "<?php if(app()->environment{$this->getDirectiveArgs($node)}): ?>";
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    protected function compileEndEnv(): string
    {
        return '<?php endif; ?>';
    }

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::NoArguments)]
    protected function compileProduction(): string
    {
        return "<?php if(app()->environment('production')): ?>";
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    protected function compileEndProduction(): string
    {
        return '<?php endif; ?>';
    }

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compileGuest(DirectiveNode $node): string
    {
        return "<?php if(auth()->guard{$this->getDirectiveArgs($node)}->guest()): ?>";
    }

    #[CompilesDirective(StructureType::Mixed, ArgumentRequirement::Required)]
    protected function compileElseGuest(DirectiveNode $node): string
    {
        return "<?php elseif(auth()->guard{$this->getDirectiveArgs($node)}->guest()): ?>";
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    protected function compileEndGuest(): string
    {
        return '<?php endif; ?>';
    }

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compileHasSection(DirectiveNode $node): string
    {
        return "<?php if (! empty(trim(\$__env->yieldContent{$this->getDirectiveArgs($node)}))): ?>";
    }

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compileSectionMissing(DirectiveNode $node): string
    {
        return "<?php if (empty(trim(\$__env->yieldContent{$this->getDirectiveArgs($node)}))): ?>";
    }

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compileIf(DirectiveNode $node): string
    {
        return "<?php if{$this->getDirectiveArgs($node)}: ?>";
    }

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compileUnless(DirectiveNode $node): string
    {
        return "<?php if (! {$this->getDirectiveArgs($node)}): ?>";
    }

    #[CompilesDirective(StructureType::Mixed, ArgumentRequirement::Required)]
    protected function compileElseIf(DirectiveNode $node): string
    {
        return "<?php elseif{$this->getDirectiveArgs($node)}: ?>";
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    protected function compileElse(): string
    {
        return '<?php else: ?>';
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    protected function compileEndif(): string
    {
        return '<?php endif; ?>';
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    protected function compileEndunless(): string
    {
        return '<?php endif; ?>';
    }

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compileIsset(DirectiveNode $node): string
    {
        return "<?php if(isset{$this->getDirectiveArgs($node)}): ?>";
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    protected function compileEndIsset(): string
    {
        return '<?php endif; ?>';
    }

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compileSwitch(DirectiveNode $node): string
    {
        $this->firstCaseInSwitch = true;

        return "<?php switch{$this->getDirectiveArgs($node)}:";
    }

    #[CompilesDirective(StructureType::Mixed, ArgumentRequirement::Required)]
    protected function compileCase(DirectiveNode $node): string
    {
        $expression = $this->getDirectiveArgs($node);

        if ($this->firstCaseInSwitch) {
            $this->firstCaseInSwitch = false;

            return "case {$expression}: ?>";
        }

        return "<?php case {$expression}: ?>";
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    protected function compileDefault(): string
    {
        return '<?php default: ?>';
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    protected function compileEndSwitch(): string
    {
        return '<?php endswitch; ?>';
    }

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Optional)]
    protected function compileOnce(DirectiveNode $node): string
    {
        $id = $node->hasArguments() ? $node->arguments->innerContent : "'".(string) Str::uuid()."'";

        return '<?php if (! $__env->hasRenderedOnce('.$id.')): $__env->markAsRenderedOnce('.$id.'); ?>';
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    public function compileEndOnce(): string
    {
        return '<?php endif; ?>';
    }

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compileSelected(DirectiveNode $node): string
    {
        return "<?php if{$this->getDirectiveArgs($node)}: echo 'selected'; endif; ?>";
    }

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compileChecked(DirectiveNode $node): string
    {
        return "<?php if{$this->getDirectiveArgs($node)}: echo 'checked'; endif; ?>";
    }

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compileDisabled(DirectiveNode $node): string
    {
        return "<?php if{$this->getDirectiveArgs($node)}: echo 'disabled'; endif; ?>";
    }

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compileRequired(DirectiveNode $node): string
    {
        return "<?php if{$this->getDirectiveArgs($node)}: echo 'required'; endif; ?>";
    }

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compileReadonly(DirectiveNode $node): string
    {
        return "<?php if{$this->getDirectiveArgs($node)}: echo 'readonly'; endif; ?>";
    }

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compilePushIf(DirectiveNode $node): string
    {
        $parts = explode(',', $this->getDirectiveArgs($node, false), 2);
        $condition = '';
        $pushContent = '';

        if (count($parts) == 2) {
            $condition = $parts[0];
            $pushContent = $parts[1];
        }

        return "<?php if({$condition}): \$__env->startPush({$pushContent}); ?>";
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    protected function compileEndPushIf(): string
    {
        return '<?php $__env->stopPush(); endif; ?>';
    }
}
