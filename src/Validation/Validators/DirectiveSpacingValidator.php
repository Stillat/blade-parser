<?php

namespace Stillat\BladeParser\Validation\Validators;

use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Validation\AbstractNodeValidator;
use Stillat\BladeParser\Validation\ValidationResult;
use Stillat\BladeParser\Validation\Validators\Concerns\CanIgnoreDirectives;

class DirectiveSpacingValidator extends AbstractNodeValidator
{
    use CanIgnoreDirectives;

    public function validate(AbstractNode $node): ?ValidationResult
    {
        if (! $node instanceof DirectiveNode || $this->shouldIgnore($node)) {
            return null;
        }

        if ($node->hasPreviousNode() && $node->hasWhitespaceOnLeft() == 0) {
            return $this->makeValidationResult($node, "Missing space before [@{$node->content}]");
        }

        if ($node->hasNextNode() && $node->hasWhitespaceOnRight() == 0) {
            return $this->makeValidationResult($node, "Missing space after [@{$node->content}]");
        }

        return null;
    }
}
