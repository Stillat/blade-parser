<?php

namespace Stillat\BladeParser\Nodes;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Stillat\BladeParser\Compiler\CompilerServices\StringSplitter;
use Stillat\BladeParser\Compiler\CompilerServices\StringUtilities;

class ArgumentGroupNode extends AbstractNode
{
    private DirectiveNode $owner;

    public string $innerContent = '';

    public ArgumentContentType $contentType = ArgumentContentType::Php;

    public function __construct(DirectiveNode $directive)
    {
        parent::__construct();

        $this->owner = $directive;
    }

    public function hasStringValue(): bool
    {
        if (! $this->contentType == ArgumentContentType::Php) {
            return false;
        }

        if (Str::startsWith($this->innerContent, "'") && Str::endsWith($this->innerContent, "'")) {
            return true;
        }

        if (Str::startsWith($this->innerContent, '"') && Str::endsWith($this->innerContent, '"')) {
            return true;
        }

        return false;
    }

    public function getStringValue(): string
    {
        if (! $this->hasStringValue()) {
            return '';
        }

        return mb_substr($this->innerContent, 1, -1);
    }

    public function setContent(string $arguments): void
    {
        $this->setIsDirty();

        $args = StringUtilities::unwrapParentheses($arguments);
        $innerContent = trim($args);
        $args = '('.$innerContent.')';

        $this->innerContent = $innerContent;
        $this->content = $args;
        $this->owner->updateSourceContent();
    }

    /**
     * Returns an array containing individual argument values.
     *
     * This method will split the content on commas, while
     * preserving arrays, nested structures and strings.
     */
    public function getValues(): Collection
    {
        if ($this->contentType == ArgumentContentType::Json) {
            return collect([$this->innerContent]);
        }

        $results = (new StringSplitter())->split($this->innerContent);
        $lastIndex = count($results) - 1;

        return collect($results)->map(function ($value, $i) use ($lastIndex) {
            if ($i == $lastIndex) {
                return $value;
            }

            // Remove the trailing ','
            return mb_substr(rtrim($value), 0, -1);
        });
    }

    public function clone(?DirectiveNode $newOwner = null): ArgumentGroupNode
    {
        $ownerToSet = $this->owner;

        if ($newOwner != null) {
            $ownerToSet = $newOwner;
        }

        $cloned = new ArgumentGroupNode($ownerToSet);
        $this->copyBasicDetailsTo($cloned);

        $cloned->innerContent = $this->innerContent;
        $cloned->contentType = $this->contentType;

        return $cloned;
    }

    public function __toString(): string
    {
        return $this->content;
    }
}
