<?php

namespace Stillat\BladeParser\Nodes;

use Stillat\BladeParser\Compiler\CompilerServices\LiteralContentHelpers;

class LiteralNode extends AbstractNode
{
    /**
     * Contains the content of the literal node, without Blade escape sequences.
     */
    public string $unescapedContent = '';

    /**
     * Contains the original leading whitespace of the node.
     */
    public string $originalLeadingWhitespace = '';

    /**
     * Contains the original trailing whitespace of the node.
     */
    public string $originalTrailingWhitespace = '';

    /**
     * Updates the content of literal node.
     *
     * If escaped Blade content is desired in the final output,
     * the content supplied to this method must include the
     * correct escape sequences in order to function.
     *
     * @param  string  $content  The new content.
     * @param  bool  $preserveOriginalWhitespace  Whether to preserve the original trailing and leading whitespace.
     */
    public function setContent(string $content, bool $preserveOriginalWhitespace = true): void
    {
        $this->setIsDirty();

        if ($preserveOriginalWhitespace) {
            $content = trim($content);
            $content = $this->originalLeadingWhitespace.$content.$this->originalTrailingWhitespace;
        }

        $this->content = $content;
        $this->unescapedContent = LiteralContentHelpers::getUnescapedContent($content);
    }

    public function clone(): LiteralNode
    {
        $literal = new LiteralNode();
        $this->copyBasicDetailsTo($literal);

        $literal->originalLeadingWhitespace = $this->originalLeadingWhitespace;
        $literal->originalTrailingWhitespace = $this->originalTrailingWhitespace;
        $literal->unescapedContent = $this->unescapedContent;

        return $literal;
    }

    public function __toString(): string
    {
        return $this->content;
    }
}
