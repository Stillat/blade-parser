<?php

namespace Stillat\BladeParser\Nodes\Components;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\Components\Concerns\ManagesComponentMetaData;
use Stillat\BladeParser\Nodes\Components\Concerns\ManagesComponentParameters;
use Stillat\BladeParser\Nodes\Concerns\ContainsDocumentText;
use Stillat\BladeParser\Nodes\Position;
use Stillat\BladeParser\Parser\ComponentParser;

class ComponentNode extends AbstractNode
{
    use ContainsDocumentText, ManagesComponentMetaData, ManagesComponentParameters;

    public string $componentPrefix = 'x';

    public bool $isCustomComponent = false;

    public bool $isSelfClosing = false;

    public bool $isClosingTag = false;

    public string $innerContent = '';

    public string $parameterContent = '';

    public string $name = '';

    public string $tagName = '';

    public ?ComponentNode $isClosedBy = null;

    public ?ComponentNode $isOpenedBy = null;

    public ?Position $namePosition = null;

    public ?Position $parameterContentPosition = null;

    /**
     * @var ParameterNode[]
     */
    public array $parameters = [];

    /**
     * The total parsed parameter count.
     */
    public int $parameterCount = 0;

    /**
     * Renames the component node.
     *
     * @param  string  $name  The new name.
     * @param  bool  $propagateChanges  Whether to push changes to any related structures.
     */
    public function rename(string $name, bool $propagateChanges = true): void
    {
        $this->tagName = ComponentParser::extractTagName($name);
        $this->name = $name;
        $this->updateMetaData();

        if ($propagateChanges && $this->isClosedBy instanceof ComponentNode) {
            $this->isClosedBy->rename($name, false);
        }

        if ($propagateChanges && $this->isOpenedBy instanceof ComponentNode) {
            $this->isOpenedBy->rename($name, false);
        }
    }

    public function getParameters(): Collection
    {
        return collect($this->parameters);
    }

    public function updateMetaData(): void
    {
        $this->parameterCount = count($this->parameters);

        $newContent = $this->getDynamicPrefix().$this->getTagName();

        if ($this->isClosingTag && $this->isSelfClosing || ! $this->isClosingTag) {
            $newContent .= ' ';
        }

        if ($this->hasParameters()) {
            $newContent .= $this->getDynamicParameterContent();

            if ($this->isSelfClosing) {
                $newContent .= ' ';
            }
        }

        $newContent .= $this->getDynamicSuffix();

        $innerContent = mb_substr($newContent, 3);

        if ($this->isSelfClosing) {
            $innerContent = mb_substr($innerContent, 0, -2);
        }

        $this->parameterContent = ' '.Str::after($innerContent, ' ');
        $this->innerContent = $innerContent;
        $this->content = $newContent;
    }

    public function getDynamicPrefix(): string
    {
        if ($this->isClosingTag && ! $this->isSelfClosing) {
            return '</'.$this->componentPrefix.'-';
        }

        return '<'.$this->componentPrefix.'-';
    }

    public function getDynamicSuffix(): string
    {
        if ($this->isSelfClosing) {
            return '/>';
        }

        return '>';
    }

    public function getDynamicParameterContent(): string
    {
        return collect($this->parameters)->map(function (ParameterNode $param) {
            return (string) $param;
        })->implode(' ');
    }

    /**
     * Returns true if the component node represents a slot component.
     */
    public function isSlot(): bool
    {
        return $this->tagName === 'slot';
    }

    /**
     * Returns the component's tag name.
     */
    public function getTagName(): string
    {
        return $this->tagName;
    }

    public function getName(): string|ParameterNode
    {
        if (! $this->isSlot()) {
            return $this->name;
        }

        if (Str::contains($this->name, ':')) {
            return Str::after($this->name, ':');
        }

        if ($this->hasParameter('name')) {
            return $this->getParameter('name');
        }

        return '';
    }

    public function clone(): ComponentNode
    {
        $component = new ComponentNode;
        $this->copyBasicDetailsTo($component);

        $component->isSelfClosing = $this->isSelfClosing;
        $component->isClosingTag = $this->isClosingTag;
        $component->innerContent = $this->innerContent;
        $component->parameterContent = $this->parameterContent;
        $component->name = $this->name;
        $component->tagName = $this->tagName;
        $component->parameterCount = $this->parameterCount;

        $component->namePosition = $this->namePosition?->clone();
        $component->parameterContentPosition = $this->parameterContentPosition?->clone();

        foreach ($this->parameters as $parameter) {
            $clonedParameter = $parameter->clone();
            $clonedParameter->setOwnerComponent($component);
            $component->parameters[] = $clonedParameter;
        }

        return $component;
    }

    public function __toString(): string
    {
        return $this->content;
    }
}
