<?php

namespace Stillat\BladeParser\Compiler\Attributes;

use Attribute;

#[Attribute]
class CompilesDirective
{
    public readonly StructureType $structureType;

    public readonly ArgumentRequirement $parameterRequirement;

    public function __construct(StructureType $structureType, ArgumentRequirement $parameterRequirement)
    {
        $this->structureType = $structureType;
        $this->parameterRequirement = $parameterRequirement;
    }
}
