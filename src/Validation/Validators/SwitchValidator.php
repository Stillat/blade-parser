<?php

namespace Stillat\BladeParser\Validation\Validators;

use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\Structures\CaseStatement;
use Stillat\BladeParser\Validation\AbstractNodeValidator;
use Stillat\BladeParser\Validation\ValidationResult;

class SwitchValidator extends AbstractNodeValidator
{
    public bool $requiresStructures = true;

    public function validate(AbstractNode $node): ?ValidationResult
    {
        if (! $node instanceof DirectiveNode || $node->getSwitchStatement() == null) {
            return null;
        }
        $switch = $node->getSwitchStatement();

        if ($switch->getCaseCount() == 0) {
            return $this->makeValidationResult($node, 'No case statements found in [@switch]');
        }

        /** @var CaseStatement $case */
        foreach ($switch->getCases() as $case) {
            $breakCount = $case->getBreakCount();

            if ($breakCount == 0) {
                return $this->makeValidationResult($case->getNode(), 'Missing [@break] statement inside [@case]');
            } elseif ($breakCount > 1) {
                return $this->makeValidationResult($case->getNode(), 'Too many [@break] statements inside [@case]');
            }
        }

        $defaultCount = $switch->getDefaultCount();

        if ($defaultCount > 1) {
            $firstDefault = $switch->getDefaultCases()->first()->getNode();

            return $this->makeValidationResult($firstDefault, 'Too many [@default] cases in [@switch]');
        }

        return null;
    }
}
