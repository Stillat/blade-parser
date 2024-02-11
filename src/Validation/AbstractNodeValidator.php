<?php

namespace Stillat\BladeParser\Validation;

use Stillat\BladeParser\Nodes\AbstractNode;

abstract class AbstractNodeValidator extends AbstractValidator
{
    /**
     * Analyzes the provided node and reports any applicable validation errors.
     *
     * Implementations may return a single `ValidationResult` instance if they
     * only need to report one result, an array of `ValidationResult` instances
     * if they wish to report multiple results at once, or may return `null`
     * if there are no results to report for the provided node.
     *
     * @param  AbstractNode  $node  The node to validate.
     */
    abstract public function validate(AbstractNode $node): ValidationResult|array|null;
}
