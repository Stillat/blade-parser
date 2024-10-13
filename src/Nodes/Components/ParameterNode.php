<?php

namespace Stillat\BladeParser\Nodes\Components;

use Illuminate\Support\Str;
use Stillat\BladeParser\Compiler\CompilerServices\StringUtilities;
use Stillat\BladeParser\Nodes\AbstractNode;

class ParameterNode extends AbstractNode
{
    private ?ComponentNode $ownerComponent = null;

    public ?ParameterAttribute $nameNode = null;

    public ?ParameterAttribute $valueNode = null;

    public ParameterType $type = ParameterType::Parameter;

    public string $name = '';

    public string $materializedName = '';

    public string $value = '';

    /**
     * Sets the node's internal dirty state.
     *
     * @param  bool  $isDirty  The dirty status.
     */
    protected function setIsDirty(bool $isDirty = true): void
    {
        parent::setIsDirty($isDirty);

        $this->ownerComponent?->setIsDirty($isDirty);
        $this->ownerComponent?->updateMetaData();
    }

    public function getNameValueDistance(): ?int
    {
        if ($this->nameNode == null || $this->valueNode == null || $this->nameNode->position == null || $this->valueNode->position == null) {
            return null;
        }
        $distance = $this->valueNode->position->startOffset - $this->nameNode->position->endOffset;

        if ($distance < 0) {
            return null;
        }

        return $distance;
    }

    private function inferValueQuoteStyle(): string
    {
        if ($this->valueNode != null && Str::startsWith($this->valueNode->content, "'")) {
            return "'";
        }

        return '"';
    }

    public function setValue(string $content): void
    {
        $valueString = StringUtilities::wrapInQuotes($content, $this->inferValueQuoteStyle());
        $paramString = $this->name.'='.$valueString;

        $this->replaceFromText($paramString);
    }

    public function setName(string $name): void
    {
        $paramString = $name;

        if ($this->hasValue()) {
            $paramString .= '='.$this->valueNode->content;
        }

        $this->replaceFromText($paramString);
    }

    public function replaceFromText(string $parameterContent): void
    {
        $parsedParameter = ParameterFactory::parameterFromText($parameterContent);

        $this->content = $parsedParameter->content;
        $this->nameNode = $parsedParameter->nameNode;
        $this->valueNode = $parsedParameter->valueNode;
        $this->type = $parsedParameter->type;
        $this->name = $parsedParameter->name;
        $this->materializedName = $parsedParameter->materializedName;
        $this->value = $parsedParameter->value;

        $this->setIsDirty();
    }

    /**
     * Tests if the parameter has a value.
     */
    public function hasValue(): bool
    {
        return $this->valueNode != null;
    }

    public function clone(): ParameterNode
    {
        $parameter = new ParameterNode;
        $this->copyBasicDetailsTo($parameter);

        $parameter->nameNode = $this->nameNode?->clone();
        $parameter->valueNode = $this->valueNode?->clone();
        $parameter->type = $this->type;
        $parameter->name = $this->name;
        $parameter->materializedName = $this->materializedName;
        $parameter->value = $this->value;

        return $parameter;
    }

    public function setOwnerComponent(?ComponentNode $componentNode): void
    {
        $this->ownerComponent = $componentNode;
    }

    public function __toString(): string
    {
        return $this->content;
    }
}
