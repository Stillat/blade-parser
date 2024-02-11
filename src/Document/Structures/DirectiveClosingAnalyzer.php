<?php

namespace Stillat\BladeParser\Document\Structures;

use Illuminate\Support\Str;
use Stillat\BladeParser\Nodes\DirectiveNode;

class DirectiveClosingAnalyzer
{
    /**
     * Analyzes the provided node to determine if it could be a closing directive.
     *
     * @param  DirectiveNode  $node  The node.
     */
    public static function analyze(DirectiveNode $node): void
    {
        $lowerName = mb_strtolower(trim($node->content));

        $node->isClosingDirective = Str::startsWith($lowerName, [
            'else', 'end',
        ]);
    }
}
