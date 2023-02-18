<?php

namespace Stillat\BladeParser\Nodes;

use Stillat\BladeParser\Compiler\CompilerServices\StringUtilities;

class PhpBlockNode extends AbstractNode
{
    /**
     * The block's inner content.
     */
    public string $innerContent = '';

    /**
     * The block's original leading whitespace.
     */
    public string $originalLeadingWhitespace = '';

    /**
     * The block's original trailing whitespace.
     */
    public string $originalTrailingWhitespace = '';

    /**
     * Updates the block's content.
     *
     * @param  string  $content The new content.
     * @param  bool  $preserveOriginalWhitespace Whether to preserve the original leading and trailing whitespace.
     */
    public function setContent(string $content, bool $preserveOriginalWhitespace = true): void
    {
        $this->setIsDirty();

        if ($preserveOriginalWhitespace) {
            $content = $this->originalLeadingWhitespace.trim($content).$this->originalTrailingWhitespace;
        }

        $content = StringUtilities::ensureStringHasWhitespace($content);

        $this->content = '@php'.$content.'@endphp';
        $this->innerContent = $content;
    }

    public function clone(): PhpBlockNode
    {
        $phpBlock = new PhpBlockNode();
        $this->copyBasicDetailsTo($phpBlock);
        $phpBlock->originalTrailingWhitespace = $this->originalTrailingWhitespace;
        $phpBlock->originalLeadingWhitespace = $this->originalLeadingWhitespace;

        $phpBlock->innerContent = $this->innerContent;

        return $phpBlock;
    }

    public function __toString(): string
    {
        return $this->content;
    }
}
