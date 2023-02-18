<?php

namespace Stillat\BladeParser\Contracts;

use Stillat\BladeParser\Nodes\Components\ComponentNode;

interface CustomComponentTagCompiler
{
    public function compile(ComponentNode $component): ?string;
}
