<?php

namespace Stillat\BladeParser\Validation\Validators;

use Illuminate\Support\Str;
use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Nodes\Components\ParameterNode;
use Stillat\BladeParser\Nodes\Components\ParameterType;
use Stillat\BladeParser\Validation\AbstractNodeValidator;
use Stillat\BladeParser\Validation\ValidationResult;

class ComponentShorthandVariableParameterValidator extends AbstractNodeValidator
{
    /**
     * Checks the node for common issues related to shorthand variable syntax.
     *
     * This validator will check for:
     * - Potential typos in shorthand variable syntax; i.e., `$:variable` instead of `:$variable`
     * - Explicitly assigning a value to a shorthand variable; i.e., `:$variable="value"`
     *
     * @param  AbstractNode  $node The node to validate.
     */
    public function validate(AbstractNode $node): ValidationResult|array|null
    {
        if (! $node instanceof  ComponentNode || ! $node->hasParameters()) {
            return null;
        }

        $issues = [];

        /** @var ParameterNode $parameter */
        foreach ($node->getParameters() as $parameter) {
            if ($parameter->type == ParameterType::Attribute && Str::startsWith($parameter->name, '$:')) {
                $didYouMean = ':$'.mb_substr($parameter->name, 2);
                $issues[] = $this->makeValidationResult($parameter, "Potential typo in shorthand parameter variable [{$parameter->name}]; did you mean [{$didYouMean}]");
            } elseif ($parameter->type == ParameterType::ShorthandDynamicVariable && $parameter->valueNode != null) {
                $issues[] = $this->makeValidationResult($parameter, "Unexpected value for shorthand parameter variable near [={$parameter->valueNode->content}]");
            }
        }

        return $issues;
    }
}
