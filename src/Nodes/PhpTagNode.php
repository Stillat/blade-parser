<?php

namespace Stillat\BladeParser\Nodes;

use Stillat\BladeParser\Compiler\CompilerServices\StringUtilities;

class PhpTagNode extends AbstractNode
{
    public PhpTagType $type = PhpTagType::PhpOpenTag;

    /**
     * The tag's inner content.
     */
    public string $innerContent = '';

    /**
     * The tag's original leading whitespace.
     */
    public string $originalLeadingWhitespace = '';

    /**
     * The tag's original trailing whitespace.
     */
    public string $originalTrailingWhitespace = '';

    private function getLeadingText(): string
    {
        if ($this->type == PhpTagType::PhpOpenTag) {
            return '<?php';
        }

        return '<?=';
    }

    /**
     * Updates the tag's content.
     *
     * This content should not start with a PHP tag, and it should not end with a closing PHP tag.
     *
     * @param  string  $content  The new content.
     * @param  bool  $preserveOriginalWhitespace  Whether to preserve original leading and trailing whitespace.
     */
    public function setContent(string $content, bool $preserveOriginalWhitespace = true): void
    {
        $this->setIsDirty();

        if ($preserveOriginalWhitespace) {
            $content = $this->originalLeadingWhitespace.trim($content).$this->originalTrailingWhitespace;
        }

        $content = StringUtilities::ensureStringHasWhitespace($content);
        $this->innerContent = $content;
        $this->content = $this->getLeadingText().$content.'?>';
    }

    /**
     * Updates the tag's type.
     *
     * @param  PhpTagType  $type  The new type.
     * @param  bool  $preserveOriginalWhitespace  Whether to preserve the tag's original leading and trailing whitespace.
     */
    public function setType(PhpTagType $type, bool $preserveOriginalWhitespace = true): void
    {
        if ($type === $this->type) {
            return;
        }
        $this->type = $type;
        $this->setContent($this->innerContent, $preserveOriginalWhitespace);
    }

    public function clone(): PhpTagNode
    {
        $phpTag = new PhpTagNode();
        $this->copyBasicDetailsTo($phpTag);
        $phpTag->innerContent = $this->innerContent;
        $phpTag->originalLeadingWhitespace = $this->originalLeadingWhitespace;
        $phpTag->originalTrailingWhitespace = $this->originalTrailingWhitespace;

        $phpTag->type = $this->type;

        return $phpTag;
    }

    public function __toString(): string
    {
        return $this->content;
    }
}
