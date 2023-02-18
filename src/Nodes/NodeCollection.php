<?php

namespace Stillat\BladeParser\Nodes;

use Illuminate\Support\Collection;
use Stillat\BladeParser\Document\NodeUtilities\QueriesGeneralNodes;

class NodeCollection extends Collection
{
    use QueriesGeneralNodes;

    /**
     * @return Collection<int, AbstractNode>
     */
    public function getNodes(): Collection
    {
        return $this;
    }
}
