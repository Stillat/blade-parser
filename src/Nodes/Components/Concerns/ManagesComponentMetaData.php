<?php

namespace Stillat\BladeParser\Nodes\Components\Concerns;

trait ManagesComponentMetaData
{
    /**
     * Returns a value indicating if the component is self-closing.
     */
    public function getIsSelfClosing(): bool
    {
        return $this->isSelfClosing;
    }

    /**
     * Returns a value indicating if the component is a closing tag.
     */
    public function getIsClosingTag(): bool
    {
        return $this->isSelfClosing;
    }

    /**
     * Returns the component's inner content.
     */
    public function getInnerContent(): string
    {
        return $this->innerContent;
    }

    /**
     * Returns the component's parameter content.
     */
    public function getParameterContent(): string
    {
        return $this->parameterContent;
    }

    /**
     * Returns the component's name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the component's tag name.
     */
    public function getTagName(): string
    {
        return mb_strtolower($this->tagName);
    }

    public function getCompareName(): string
    {
        return mb_strtolower($this->componentPrefix.'-'.$this->tagName);
    }
}
