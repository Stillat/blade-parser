<?php

namespace Stillat\BladeParser\Nodes;

use Stillat\BladeParser\Compiler\CompilerServices\StringUtilities;

class VerbatimNode extends AbstractNode
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
     * @param  bool  $preserveOriginalWhitespace Whether to preserve original leading and trailing whitespace.
     */
    public function setContent(string $content, bool $preserveOriginalWhitespace = true): void
    {
        $this->setIsDirty();

        if ($preserveOriginalWhitespace) {
            $content = $this->originalLeadingWhitespace.trim($content).$this->originalTrailingWhitespace;
        }

        $content = StringUtilities::ensureStringHasWhitespace($content);

        $this->content = '@verbatim'.$content.'@endverbatim';
        $this->innerContent = $content;
    }

    public function clone(): VerbatimNode
    {
        $verbatim = new VerbatimNode();
        $this->copyBasicDetailsTo($verbatim);
        $verbatim->originalTrailingWhitespace = $this->originalTrailingWhitespace;
        $verbatim->originalLeadingWhitespace = $this->originalLeadingWhitespace;

        $verbatim->innerContent = $this->innerContent;

        return $verbatim;
    }

    public function __toString(): string
    {
        return $this->content;
    }
}
