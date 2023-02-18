<?php

namespace Stillat\BladeParser\Validation\Validators;

use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\Structures\ConditionalBranch;
use Stillat\BladeParser\Validation\AbstractNodeValidator;
use Stillat\BladeParser\Validation\ValidationResult;
use Stillat\BladeParser\Validation\Validators\Concerns\CanIgnoreDirectives;

class DuplicateConditionExpressionsValidator extends AbstractNodeValidator
{
    use CanIgnoreDirectives;

    public bool $requiresStructures = true;

    public function validate(AbstractNode $node): ?ValidationResult
    {
        if (! $node instanceof DirectiveNode || ! $node->getIsConditionDirective() || $this->shouldIgnore($node)) {
            return null;
        }
        if ($node->isOpenedBy != null || $node->getCondition() == null) {
            return null;
        }

        $conditions = [];

        /** @var ConditionalBranch $branch */
        foreach ($node->getCondition()->getBranches() as $branch) {
            $branchText = trim($branch->getText());

            if (mb_strlen($branchText) == 0) {
                continue;
            }

            if (in_array($branchText, $conditions)) {
                return $this->makeValidationResult($branch->target, "Duplicate expression [{$branchText}] in [@{$branch->target->content}]");
            }

            $conditions[] = $branchText;
        }

        return null;
    }
}
