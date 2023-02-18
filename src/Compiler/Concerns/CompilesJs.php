<?php

namespace Stillat\BladeParser\Compiler\Concerns;

use Illuminate\Support\Js;
use Stillat\BladeParser\Compiler\Attributes\ArgumentRequirement;
use Stillat\BladeParser\Compiler\Attributes\CompilesDirective;
use Stillat\BladeParser\Compiler\Attributes\StructureType;
use Stillat\BladeParser\Nodes\DirectiveNode;

trait CompilesJs
{
    #[CompilesDirective(StructureType::EchoHelper, ArgumentRequirement::Required)]
    protected function compileJs(DirectiveNode $node): string
    {
        return sprintf(
            "<?php echo \%s::from(%s)->toHtml() ?>",
            Js::class, $this->getDirectiveArgsInnerContent($node)
        );
    }
}
