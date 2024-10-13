<?php

namespace Stillat\BladeParser\Tests\Compiler;

use Stillat\BladeParser\Compiler\Transformers\NodeTransformer;
use Stillat\BladeParser\Nodes\DirectiveNode;

class CustomTransformer extends NodeTransformer
{
    public function transformNode($node): ?string
    {
        if (! $node instanceof DirectiveNode || $node->content != 'custom') {
            return null;
        }

        $this->skipToNode($node->isClosedBy);

        return '@include("something-here")';
    }
}
