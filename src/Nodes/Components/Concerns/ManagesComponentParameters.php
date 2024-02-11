<?php

namespace Stillat\BladeParser\Nodes\Components\Concerns;

use Stillat\BladeParser\Errors\Exceptions\DuplicateParameterException;
use Stillat\BladeParser\Errors\Exceptions\InvalidParameterException;
use Stillat\BladeParser\Nodes\Components\ParameterFactory;
use Stillat\BladeParser\Nodes\Components\ParameterNode;

trait ManagesComponentParameters
{
    /**
     * Adds a parameter from text.
     *
     * @param  string  $parameterContent  The parameter content.
     */
    public function addParameterFromText(string $parameterContent): void
    {
        $param = ParameterFactory::parameterFromText($parameterContent);

        if ($param == null) {
            throw new InvalidParameterException();
        }

        $this->addParameter($param);
    }

    /**
     * Adds a new parameter to the component.
     *
     * @param  ParameterNode  $parameter  The new parameter.
     *
     * @throws \Stillat\BladeParser\Errors\Exceptions\DuplicateParameterException
     */
    public function addParameter(ParameterNode $parameter): void
    {
        if ($this->hasParameterInstance($parameter)) {
            throw new DuplicateParameterException('Component already contains parameter instance ['.$parameter->name.']');
        }
        $this->setIsDirty();

        $parameter->setOwnerComponent($this);
        $this->parameters[] = $parameter;

        $this->updateMetaData();
    }

    /**
     * Removes a parameter instance from the component.
     *
     * @param  ParameterNode  $parameter  The parameter to remove.
     */
    public function removeParameter(ParameterNode $parameter): void
    {
        $this->setIsDirty();
        $this->parameters = collect($this->parameters)->filter(function (ParameterNode $node) use ($parameter) {
            if ($node === $parameter) {
                $node->setOwnerComponent(null);
            }

            return $parameter !== $node;
        })->all();

        $this->updateMetaData();
    }

    /**
     * Tests if the component contains the provided parameter instance.
     *
     * @param  ParameterNode  $parameter  The parameter.
     */
    public function hasParameterInstance(ParameterNode $parameter): bool
    {
        return collect($this->parameters)->filter(function (ParameterNode $param) use (&$parameter) {
            return $param === $parameter;
        })->count() > 0;
    }

    /**
     * Tests if the component contains a parameter with the provided name.
     *
     * @param  string  $name  The parameter name.
     */
    public function hasParameter(string $name): bool
    {
        return $this->getParameter($name) != null;
    }

    /**
     * Gets the parameter with the provided name.
     *
     * @param  string  $name  The parameter name.
     */
    public function getParameter(string $name): ?ParameterNode
    {
        foreach ($this->parameters as $parameter) {
            if ($parameter->materializedName == $name) {
                return $parameter;
            }
        }

        return null;
    }

    /**
     * Returns a value indicating if the component has any parameters.
     */
    public function hasParameters(): bool
    {
        return $this->parameterCount > 0;
    }
}
