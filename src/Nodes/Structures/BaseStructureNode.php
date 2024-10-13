<?php

namespace Stillat\BladeParser\Nodes\Structures;

use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\BaseNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\NodeCollection;

class BaseStructureNode extends BaseNode
{
    public ?DirectiveNode $constructedFrom = null;

    public function getParent(): ?AbstractNode
    {
        return $this->constructedFrom?->getParent();
    }

    public function getNode(): ?DirectiveNode
    {
        return $this->constructedFrom;
    }

    public function getNodes(): NodeCollection
    {
        return $this->constructedFrom->getNodes();
    }

    public function resolveStructures(): void {}
}
