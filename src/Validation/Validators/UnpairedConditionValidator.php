<?php

namespace Stillat\BladeParser\Validation\Validators;

use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Validation\AbstractNodeValidator;
use Stillat\BladeParser\Validation\ValidationResult;
use Stillat\BladeParser\Validation\Validators\Concerns\CanIgnoreDirectives;

class UnpairedConditionValidator extends AbstractNodeValidator
{
    use CanIgnoreDirectives;

    public bool $requiresStructures = true;

    public function validate(AbstractNode $node): ?ValidationResult
    {
        if (! $node instanceof DirectiveNode || ! $node->getIsConditionDirective() || $this->shouldIgnore($node) || ! $node->getConditionRequiresClose() || $node->isClosedBy != null) {
            return null;
        }

        return $this->makeValidationResult($node, "Unpaired condition [@{$node->content}]");
    }
}
