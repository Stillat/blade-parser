<?php

namespace Stillat\BladeParser\Nodes;

use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;

class BaseNode
{
    use Macroable;

    /**
     * Identifies the original position within
     * the document, relative to other nodes.
     */
    public int $index = 0;

    /**
     * An arbitrary identifier assigned to each node.
     */
    public string $id = '';

    /**
     * The node's position within the source template.
     */
    public ?Position $position = null;

    /**
     * The node's content.
     */
    public string $content = '';

    public function __construct()
    {
        $this->id = 'N'.rand(0, 9999).Str::uuid();
    }

    public function getStartIndentationLevel(): ?int
    {
        return $this->position?->startColumn - 1;
    }

    /**
     * Clones basic details to the provided target node.
     *
     * @param  BaseNode  $node  The target node to copy to.
     */
    public function copyBasicDetailsTo(BaseNode $node): void
    {
        $node->index = $this->index;
        $node->position = $this->position?->clone();
        $node->content = $this->content;
    }
}
