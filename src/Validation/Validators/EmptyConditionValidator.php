<?php

namespace Stillat\BladeParser\Validation\Validators;

use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Parser\BladeKeywords;
use Stillat\BladeParser\Validation\AbstractNodeValidator;
use Stillat\BladeParser\Validation\ValidationResult;
use Stillat\BladeParser\Validation\Validators\Concerns\CanIgnoreDirectives;

class EmptyConditionValidator extends AbstractNodeValidator
{
    use CanIgnoreDirectives;

    public bool $requiresStructures = true;

    public function validate(AbstractNode $node): ?ValidationResult
    {
        if (! $node instanceof DirectiveNode || ! $node->getIsConditionDirective()) {
            return null;
        }
        if ($node->content == BladeKeywords::K_ELSE || (! $node->getConditionRequiresClose() && $node->getConditionStructureName() != BladeKeywords::K_ElseIf)) {
            return null;
        }
        if ($this->shouldIgnore($node)) {
            return null;
        }

        if (mb_strlen($this->getDirectiveArgContents($node)) > 0) {
            return null;
        }

        return $this->makeValidationResult($node, "Invalid empty expression for [@{$node->content}]");
    }
}
