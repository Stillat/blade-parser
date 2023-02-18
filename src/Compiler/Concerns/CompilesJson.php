<?php

namespace Stillat\BladeParser\Compiler\Concerns;

use Stillat\BladeParser\Compiler\Attributes\ArgumentRequirement;
use Stillat\BladeParser\Compiler\Attributes\CompilesDirective;
use Stillat\BladeParser\Compiler\Attributes\StructureType;
use Stillat\BladeParser\Nodes\DirectiveNode;

trait CompilesJson
{
    /**
     * The default JSON encoding options.
     */
    private int $encodingOptions = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;

    #[CompilesDirective(StructureType::EchoHelper, ArgumentRequirement::Required)]
    protected function compileJson(DirectiveNode $node): string
    {
        $parts = explode(',', $this->getDirectiveArgsInnerContent($node));

        $options = isset($parts[1]) ? trim($parts[1]) : $this->encodingOptions;

        $depth = isset($parts[2]) ? trim($parts[2]) : 512;

        return "<?php echo json_encode($parts[0], $options, $depth) ?>";
    }
}
