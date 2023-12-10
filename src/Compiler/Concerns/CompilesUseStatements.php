<?php

namespace Stillat\BladeParser\Compiler\Concerns;

use Illuminate\Contracts\View\ViewCompilationException;
use Stillat\BladeParser\Compiler\Attributes\ArgumentRequirement;
use Stillat\BladeParser\Compiler\Attributes\CompilesDirective;
use Stillat\BladeParser\Compiler\Attributes\StructureType;
use Stillat\BladeParser\Compiler\CompilerServices\StringUtilities;
use Stillat\BladeParser\Nodes\DirectiveNode;

trait CompilesUseStatements
{
    #[CompilesDirective(StructureType::Mixed, ArgumentRequirement::Required)]
    protected function compileUse(DirectiveNode $node): string
    {
        if (! $node->hasArguments()) {
            throw new ViewCompilationException('Missing arguments for @use statement.');
        }

        $args = $node->arguments->getArgValues();

        if (count($args) > 2) {
            throw new ViewCompilationException('Too many arguments for @use statement.');
        }

        $args[0] = StringUtilities::unwrapString($args[0]);

        if (count($args) === 1) {
            return '<?php use \\'.$args[0].'; ?>';
        }

        $args[1] = StringUtilities::unwrapString($args[1] ?? '');

        return '<?php use \\'.$args[0].' as '.$args[1].'; ?>';
    }
}
