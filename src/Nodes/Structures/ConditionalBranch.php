<?php

namespace Stillat\BladeParser\Nodes\Structures;

use Stillat\BladeParser\Compiler\CompilerServices\StringUtilities;
use Stillat\BladeParser\Document\NodeUtilities\QueriesGeneralNodes;
use Stillat\BladeParser\Nodes\BaseNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\NodeCollection;
use Stillat\BladeParser\Parser\BladeKeywords;

class ConditionalBranch extends BaseNode
{
    use QueriesGeneralNodes;

    public DirectiveNode $target;

    public function __construct(DirectiveNode $target)
    {
        parent::__construct();

        $this->target = $target;
    }

    public function getRootNodes(): NodeCollection
    {
        return $this->target->getRootNodes();
    }

    public function getDirectChildren(): NodeCollection
    {
        return $this->target->getDirectChildren();
    }

    public function isEmpty(): bool
    {
        if ($this->target->content == BladeKeywords::K_ELSE) {
            return false;
        }
        if (! $this->target->hasArguments()) {
            return true;
        }

        return mb_strlen($this->getText()) == 0;
    }

    public function getText(): string
    {
        if (! $this->target->hasArguments()) {
            return '';
        }

        return StringUtilities::unwrapParentheses($this->target->getValue());
    }
}
