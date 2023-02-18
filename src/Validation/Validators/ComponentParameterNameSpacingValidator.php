<?php

namespace Stillat\BladeParser\Validation\Validators;

use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Nodes\Components\ParameterNode;
use Stillat\BladeParser\Validation\AbstractNodeValidator;
use Stillat\BladeParser\Validation\ValidationResult;

class ComponentParameterNameSpacingValidator extends AbstractNodeValidator
{
    /**
     * Validates that no spaces appear between a parameter's name and value within a component.
     *
     * The following would *not* trigger a validation result:
     *    `<x-profile message="The message" />`
     *
     * However, the following example would:
     *    `<x-profile message = "The message" />`
     *
     * @param  AbstractNode  $node The node to test.
     */
    public function validate(AbstractNode $node): ValidationResult|array|null
    {
        if (! $node instanceof ComponentNode) {
            return null;
        }

        if (! $node->hasParameters()) {
            return null;
        }

        $issues = [];

        $params = $node->getParameters()->filter(fn (ParameterNode $param) => $param->hasValue());

        /** @var ParameterNode $param */
        foreach ($params as $param) {
            $distance = $param->getNameValueDistance();

            if ($distance == null || $distance <= 2) {
                continue;
            }

            $issues = $this->makeValidationResult($param, "Invalid spacing between component parameter name/value near [{$param->name}]");
        }

        return $issues;
    }
}
