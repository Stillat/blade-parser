<?php

namespace Stillat\BladeParser\Compiler;

use Stillat\BladeParser\Nodes\AbstractNode;

class AppendState
{
    /**
     * @param  AbstractNode  $node The node that was compiled.
     * @param  int  $beforeLineNumber The last line number in the compiled document before the node was compiled.
     * @param  int  $afterLineNumber The last line number in the compiled document after the node was compiled.
     * @param  string  $value The node's compiled value.
     */
    public function __construct(public AbstractNode $node,
                                public int $beforeLineNumber,
                                public int $afterLineNumber,
                                public string $value)
    {
    }
}
