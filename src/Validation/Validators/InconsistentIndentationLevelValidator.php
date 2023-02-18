<?php

namespace Stillat\BladeParser\Validation\Validators;

use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Validation\AbstractNodeValidator;
use Stillat\BladeParser\Validation\ValidationResult;
use Stillat\BladeParser\Validation\Validators\Concerns\CanIgnoreDirectives;

class InconsistentIndentationLevelValidator extends AbstractNodeValidator
{
    use CanIgnoreDirectives;

    public bool $requiresStructures = true;

    public function validate(AbstractNode $node): ValidationResult|array|null
    {
        if (! $node instanceof DirectiveNode || $this->shouldIgnore($node)) {
            return null;
        }
        if ($node->isOpenedBy == null) {
            return null;
        }
        if ($node->getStartIndentationLevel() === null || $node->isOpenedBy->getStartIndentationLevel() === null) {
            return null;
        }

        $currentIndentation = $node->getStartIndentationLevel();
        $previousIndentation = $node->isOpenedBy->getStartIndentationLevel();

        if ($currentIndentation === $previousIndentation) {
            return null;
        }

        return $this->makeValidationResult($node, "Inconsistent indentation level of {$currentIndentation} for [@{$node->content}]; parent [@{$node->isOpenedBy->content}] has a level of {$previousIndentation}");
    }
}
