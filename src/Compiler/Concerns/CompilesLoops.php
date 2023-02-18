<?php

namespace Stillat\BladeParser\Compiler\Concerns;

use Illuminate\Contracts\View\ViewCompilationException;
use Stillat\BladeParser\Compiler\Attributes\ArgumentRequirement;
use Stillat\BladeParser\Compiler\Attributes\CompilesDirective;
use Stillat\BladeParser\Compiler\Attributes\StructureType;
use Stillat\BladeParser\Nodes\DirectiveNode;

trait CompilesLoops
{
    protected int $forElseCounter = 0;

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compileForelse(DirectiveNode $node): string
    {
        if (! $node->hasArguments()) {
            throw new ViewCompilationException('Malformed @forelse statement.');
        }

        $empty = '$__empty_'.++$this->forElseCounter;

        $result = $this->loopExtractor->extractDetails($this->getDirectiveArgs($node));

        if (! $result->isValid) {
            throw new ViewCompilationException('Malformed @forelse statement.');
        }

        $initLoop = "\$__currentLoopData = {$result->variable}; \$__env->addLoop(\$__currentLoopData);";
        $iterateLoop = '$__env->incrementLoopIndices(); $loop = $__env->getLastLoop();';

        return "<?php {$empty} = true; {$initLoop} foreach(\$__currentLoopData as {$result->alias}): {$iterateLoop} {$empty} = false; ?>";
    }

    #[CompilesDirective(StructureType::Mixed, ArgumentRequirement::Optional)]
    protected function compileEmpty(DirectiveNode $node): string
    {
        if ($node->hasArguments()) {
            $expression = $node->arguments->content;

            return "<?php if(empty{$expression}): ?>";
        }

        $empty = '$__empty_'.$this->forElseCounter--;

        return "<?php endforeach; \$__env->popLoop(); \$loop = \$__env->getLastLoop(); if ({$empty}): ?>";
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    protected function compileEndforelse(): string
    {
        return '<?php endif; ?>';
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    protected function compileEndEmpty(): string
    {
        return '<?php endif; ?>';
    }

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compileFor(DirectiveNode $node): string
    {
        return "<?php for{$this->getDirectiveArgs($node)}: ?>";
    }

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compileForeach(DirectiveNode $node): string
    {
        if (! $node->hasArguments()) {
            throw new ViewCompilationException('Malformed @foreach statement.');
        }

        $result = $this->loopExtractor->extractDetails($this->getDirectiveArgs($node));

        if (! $result->isValid) {
            throw new ViewCompilationException('Malformed @foreach statement.');
        }

        $initLoop = "\$__currentLoopData = {$result->variable}; \$__env->addLoop(\$__currentLoopData);";

        $iterateLoop = '$__env->incrementLoopIndices(); $loop = $__env->getLastLoop();';

        return "<?php {$initLoop} foreach(\$__currentLoopData as {$result->alias}): {$iterateLoop} ?>";
    }

    #[CompilesDirective(StructureType::Mixed, ArgumentRequirement::Optional)]
    protected function compileBreak(DirectiveNode $node): string
    {
        if (! $node->hasArguments()) {
            return '<?php break; ?>';
        }

        $expression = $this->getDirectiveArgs($node);

        if ($expression) {
            preg_match('/\(\s*(-?\d+)\s*\)$/', $expression, $matches);

            return $matches ? '<?php break '.max(1, $matches[1]).'; ?>' : "<?php if{$expression} break; ?>";
        }

        return '<?php break; ?>';
    }

    #[CompilesDirective(StructureType::Mixed, ArgumentRequirement::Optional)]
    protected function compileContinue(DirectiveNode $node): string
    {
        if (! $node->hasArguments()) {
            return '<?php continue; ?>';
        }

        $expression = $this->getDirectiveArgs($node);

        if ($expression) {
            preg_match('/\(\s*(-?\d+)\s*\)$/', $expression, $matches);

            return $matches ? '<?php continue '.max(1, $matches[1]).'; ?>' : "<?php if{$expression} continue; ?>";
        }

        return '<?php continue; ?>';
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    protected function compileEndfor(): string
    {
        return '<?php endfor; ?>';
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    protected function compileEndforeach(): string
    {
        return '<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>';
    }

    #[CompilesDirective(StructureType::Open, ArgumentRequirement::Required)]
    protected function compileWhile(DirectiveNode $node): string
    {
        return "<?php while{$this->getDirectiveArgs($node)}: ?>";
    }

    #[CompilesDirective(StructureType::Terminator, ArgumentRequirement::NoArguments)]
    protected function compileEndwhile(): string
    {
        return '<?php endwhile; ?>';
    }
}
