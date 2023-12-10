<?php

namespace Stillat\BladeParser\Compiler\Concerns;

use Illuminate\Support\Str;
use Stillat\BladeParser\Compiler\Attributes\ArgumentRequirement;
use Stillat\BladeParser\Compiler\Attributes\CompilesDirective;
use Stillat\BladeParser\Compiler\Attributes\StructureType;
use Stillat\BladeParser\Compiler\Compiler;
use Stillat\BladeParser\Nodes\DirectiveNode;

trait CompilesComponents
{
    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compileComponent(DirectiveNode $node): string
    {
        $expression = $this->getDirectiveArgs($node, false);

        [$component, $alias, $data] = str_contains($expression, ',')
            ? array_map('trim', explode(',', trim($expression, '()'), 3)) + ['', '', '']
            : [trim($expression, '()'), '', ''];

        $component = trim($component, '\'"');

        $hash = Compiler::newComponentHash($component);

        if (Str::contains($component, ['::class', '\\'])) {
            return Compiler::compileClassComponentOpening($component, $alias, $data, $hash);
        }

        return "<?php \$__env->startComponent{$expression}; ?>";
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    protected function compileEndComponent(): string
    {
        return '<?php echo $__env->renderComponent(); ?>';
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    public function compileEndComponentClass(): string
    {
        $hash = array_pop(Compiler::$componentHashStack);

        return $this->compileEndComponent()."\n".implode("\n", [
            '<?php endif; ?>',
            '<?php if (isset($__attributesOriginal'.$hash.')): ?>',
            '<?php $attributes = $__attributesOriginal'.$hash.'; ?>',
            '<?php unset($__attributesOriginal'.$hash.'); ?>',
            '<?php endif; ?>',
            '<?php if (isset($__componentOriginal'.$hash.')): ?>',
            '<?php $component = $__componentOriginal'.$hash.'; ?>',
            '<?php unset($__componentOriginal'.$hash.'); ?>',
            '<?php endif; ?>',
        ]);
    }

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compileSlot(DirectiveNode $node): string
    {
        $expression = $this->getDirectiveArgs($node);

        return "<?php \$__env->slot{$expression}; ?>";
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    protected function compileEndSlot(): string
    {
        return '<?php $__env->endSlot(); ?>';
    }

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compileComponentFirst(DirectiveNode $node): string
    {
        return "<?php \$__env->startComponentFirst{$this->getDirectiveArgs($node)}; ?>";
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    protected function compileEndComponentFirst(): string
    {
        return $this->compileEndComponent();
    }

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compileProps(DirectiveNode $node): string
    {
        $expression = $this->getDirectiveArgs($node, false);

        return "<?php \$attributes ??= new \\Illuminate\\View\\ComponentAttributeBag; ?>
<?php foreach(\$attributes->onlyProps{$expression} as \$__key => \$__value) {
    \$\$__key = \$\$__key ?? \$__value;
} ?>
<?php \$attributes = \$attributes->exceptProps{$expression}; ?>
<?php foreach (array_filter({$expression}, 'is_string', ARRAY_FILTER_USE_KEY) as \$__key => \$__value) {
    \$\$__key = \$\$__key ?? \$__value;
} ?>
<?php \$__defined_vars = get_defined_vars(); ?>
<?php foreach (\$attributes as \$__key => \$__value) {
    if (array_key_exists(\$__key, \$__defined_vars)) unset(\$\$__key);
} ?>
<?php unset(\$__defined_vars); ?>";
    }

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compileAware(DirectiveNode $node): string
    {
        $expression = $this->getDirectiveArgs($node, false);

        return "<?php foreach ({$expression} as \$__key => \$__value) {
    \$__consumeVariable = is_string(\$__key) ? \$__key : \$__value;
    \$\$__consumeVariable = is_string(\$__key) ? \$__env->getConsumableComponentData(\$__key, \$__value) : \$__env->getConsumableComponentData(\$__value);
} ?>";
    }
}
