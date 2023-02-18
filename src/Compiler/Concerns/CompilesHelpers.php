<?php

namespace Stillat\BladeParser\Compiler\Concerns;

use Illuminate\Foundation\Vite;
use Stillat\BladeParser\Compiler\Attributes\ArgumentRequirement;
use Stillat\BladeParser\Compiler\Attributes\CompilesDirective;
use Stillat\BladeParser\Compiler\Attributes\StructureType;
use Stillat\BladeParser\Nodes\DirectiveNode;

trait CompilesHelpers
{
    #[CompilesDirective(StructureType::EchoHelper, ArgumentRequirement::NoArguments)]
    protected function compileCsrf(): string
    {
        return '<?php echo csrf_field(); ?>';
    }

    #[CompilesDirective(StructureType::Debug, ArgumentRequirement::Required)]
    protected function compileDd(DirectiveNode $node): string
    {
        return "<?php dd{$this->getDirectiveArgs($node)}; ?>";
    }

    #[CompilesDirective(StructureType::Debug, ArgumentRequirement::Required)]
    protected function compileDump(DirectiveNode $node): string
    {
        return "<?php dump{$this->getDirectiveArgs($node)}; ?>";
    }

    #[CompilesDirective(StructureType::EchoHelper, ArgumentRequirement::Required)]
    protected function compileMethod(DirectiveNode $node): string
    {
        return "<?php echo method_field{$this->getDirectiveArgs($node)}; ?>";
    }

    #[CompilesDirective(StructureType::EchoHelper, ArgumentRequirement::Required)]
    protected function compileVite(DirectiveNode $node): string
    {
        $class = Vite::class;

        return "<?php echo app('$class'){$this->getDirectiveArgs($node)}; ?>";
    }

    #[CompilesDirective(StructureType::EchoHelper, ArgumentRequirement::NoArguments)]
    protected function compileViteReactRefresh(): string
    {
        $class = Vite::class;

        return "<?php echo app('$class')->reactRefresh(); ?>";
    }
}
