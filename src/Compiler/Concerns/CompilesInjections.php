<?php

namespace Stillat\BladeParser\Compiler\Concerns;

use Stillat\BladeParser\Compiler\Attributes\ArgumentRequirement;
use Stillat\BladeParser\Compiler\Attributes\CompilesDirective;
use Stillat\BladeParser\Compiler\Attributes\StructureType;
use Stillat\BladeParser\Nodes\DirectiveNode;

trait CompilesInjections
{
    #[CompilesDirective(StructureType::Mixed, ArgumentRequirement::Required)]
    protected function compileInject(DirectiveNode $node): string
    {
        $expression = $this->getDirectiveArgs($node);

        $segments = explode(',', preg_replace("/[\(\)]/", '', $expression));

        $variable = '';
        $service = '';

        if (count($segments) == 2) {
            $variable = trim($segments[0], " '\"");
            $service = trim($segments[1]);
        }

        return "<?php \${$variable} = app({$service}); ?>";
    }
}
