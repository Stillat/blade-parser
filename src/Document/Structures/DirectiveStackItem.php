<?php

namespace Stillat\BladeParser\Document\Structures;

use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\DirectiveNode;

class DirectiveStackItem
{
    /**
     * @var AbstractNode[]
     */
    public array $documentNodes = [];

    public ?DirectiveNode $refParent = null;
}
