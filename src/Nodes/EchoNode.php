<?php

namespace Stillat\BladeParser\Nodes;

class EchoNode extends AbstractNode
{
    /**
     * The echo statement's inner content.
     */
    public string $innerContent = '';

    /**
     * The echo node's type.
     */
    public EchoType $type = EchoType::Echo;

    private function getPrefix(): string
    {
        return match ($this->type) {
            EchoType::RawEcho => '{!!',
            EchoType::TripleEcho => '{{{',
            default => '{{',
        };
    }

    private function getSuffix(): string
    {
        return match ($this->type) {
            EchoType::RawEcho => '!!}',
            EchoType::TripleEcho => '}}}',
            default => '}}',
        };
    }

    /**
     * Sets the node's type.
     *
     * @param  EchoType  $type  The type.
     */
    public function setType(EchoType $type): void
    {
        $this->type = $type;
        $this->setInnerContent($this->innerContent);
    }

    /**
     * Sets the inner content of the echo node.
     *
     * @param  string  $innerContent  The new content.
     */
    public function setInnerContent(string $innerContent): void
    {
        $this->setIsDirty();
        $innerContent = trim($innerContent);
        $this->innerContent = ' '.$innerContent.' ';
        $this->content = $this->getPrefix().$this->innerContent.$this->getSuffix();
    }

    public function clone(): EchoNode
    {
        $echo = new EchoNode();
        $this->copyBasicDetailsTo($echo);

        $echo->innerContent = $this->innerContent;

        return $echo;
    }

    public function __toString(): string
    {
        return $this->content;
    }
}
