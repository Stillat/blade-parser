<?php

namespace Stillat\BladeParser\Nodes;

class CommentNode extends AbstractNode
{
    /**
     * The comment's inner content.
     */
    public string $innerContent = '';

    /**
     * The comment's original leading whitespace.
     */
    public string $originalLeadingWhitespace = '';

    /**
     * The comment's original trailing whitespace.
     */
    public string $originalTrailingWhitespace = '';

    /**
     * Updates the comment's inner content.
     *
     * @param  string  $content The comment's content.
     * @param  bool  $preserveOriginalWhitespace Whether to preserve the original leading and trailing whitespace.
     */
    public function setContent(string $content, bool $preserveOriginalWhitespace = true): void
    {
        $this->setIsDirty();

        if ($preserveOriginalWhitespace) {
            $content = trim($content);
            $content = $this->originalLeadingWhitespace.$content.$this->originalTrailingWhitespace;
        }

        // Prevent comment start/ends from ending up in the final content.
        $content = str_replace('{{--', '', $content);
        $content = str_replace('--}}', '', $content);

        if (mb_strlen(ltrim($content)) == mb_strlen($content)) {
            $content = ' '.$content;
        }

        if (mb_strlen(rtrim($content)) == mb_strlen($content)) {
            $content = $content.' ';
        }

        $this->innerContent = $content;
        $this->content = '{{--'.$this->innerContent.'--}}';
    }

    public function clone(): CommentNode
    {
        $commentNode = new CommentNode();
        $this->copyBasicDetailsTo($commentNode);

        $commentNode->innerContent = $this->innerContent;

        return $commentNode;
    }

    public function __toString(): string
    {
        return $this->content;
    }
}
