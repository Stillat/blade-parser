<?php

namespace Stillat\BladeParser\Tests\Compiler;

use Stillat\BladeParser\Contracts\CustomComponentTagCompiler;
use Stillat\BladeParser\Nodes\Components\ComponentNode;

class CustomCompiler implements CustomComponentTagCompiler
{
    function compile(ComponentNode $component): ?string
    {
        return 'My custom compilation result!';
    }
}
