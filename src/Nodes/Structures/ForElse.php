<?php

namespace Stillat\BladeParser\Nodes\Structures;

use Stillat\BladeParser\Document\NodeUtilities\QueriesGeneralNodes;
use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\NodeCollection;
use Stillat\BladeParser\Parser\BladeKeywords;

class ForElse extends BaseStructureNode
{
    use QueriesGeneralNodes;

    public ?DirectiveNode $emptyDirective;

    public array $loopBody = [];

    public array $emptyBody = [];

    public function getRootNodes(): NodeCollection
    {
        return $this->constructedFrom->getRootNodes();
    }

    public function getDirectChildren(): NodeCollection
    {
        return $this->constructedFrom->getDirectChildren();
    }

    public function hasEmptyClause(): bool
    {
        return $this->emptyDirective != null;
    }

    public function getLoopBody(): NodeCollection
    {
        return new NodeCollection($this->loopBody);
    }

    public function getEmptyBody(): NodeCollection
    {
        return new NodeCollection($this->emptyBody);
    }

    public function getEmptyDirectiveCount(): int
    {
        $initialCount = 0;

        if ($this->emptyDirective != null) {
            $initialCount = 1;
        }

        return $initialCount + $this->getEmptyBody()->filter(function (AbstractNode $node) {
            return $node instanceof DirectiveNode && $node->content == BladeKeywords::K_Empty;
        })->count();
    }

    public function isValid(): bool
    {
        return $this->getEmptyDirectiveCount() == 1;
    }
}
