<?php

namespace Stillat\BladeParser\Compiler\Concerns;

use Illuminate\Support\Str;
use Stillat\BladeParser\Compiler\Attributes\ArgumentRequirement;
use Stillat\BladeParser\Compiler\Attributes\CompilesDirective;
use Stillat\BladeParser\Compiler\Attributes\StructureType;
use Stillat\BladeParser\Nodes\DirectiveNode;

trait CompilesStacks
{
    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compileStack(DirectiveNode $node): string
    {
        return "<?php echo \$__env->yieldPushContent{$this->getDirectiveArgs($node)}; ?>";
    }

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compilePush(DirectiveNode $node): string
    {
        return "<?php \$__env->startPush{$this->getDirectiveArgs($node)}; ?>";
    }

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compilePushOnce(DirectiveNode $node): string
    {
        $parts = explode(',', $this->getDirectiveArgsInnerContent($node), 2);

        [$stack, $id] = [$parts[0], $parts[1] ?? ''];

        $id = trim($id) ?: "'".(string) Str::uuid()."'";

        return '<?php if (! $__env->hasRenderedOnce('.$id.')): $__env->markAsRenderedOnce('.$id.');
$__env->startPush('.$stack.'); ?>';
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    protected function compileEndpush(): string
    {
        return '<?php $__env->stopPush(); ?>';
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    protected function compileEndpushOnce(): string
    {
        return '<?php $__env->stopPush(); endif; ?>';
    }

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compilePrepend(DirectiveNode $node): string
    {
        return "<?php \$__env->startPrepend{$this->getDirectiveArgs($node)}; ?>";
    }

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compilePrependOnce(DirectiveNode $node): string
    {
        $parts = explode(',', $this->getDirectiveArgsInnerContent($node), 2);

        [$stack, $id] = [$parts[0], $parts[1] ?? ''];

        $id = trim($id) ?: "'".(string) Str::uuid()."'";

        return '<?php if (! $__env->hasRenderedOnce('.$id.')): $__env->markAsRenderedOnce('.$id.');
$__env->startPrepend('.$stack.'); ?>';
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    protected function compileEndprepend(): string
    {
        return '<?php $__env->stopPrepend(); ?>';
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    protected function compileEndprependOnce(): string
    {
        return '<?php $__env->stopPrepend(); endif; ?>';
    }
}
