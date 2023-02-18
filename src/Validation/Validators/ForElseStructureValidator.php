<?php

namespace Stillat\BladeParser\Validation\Validators;

use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Validation\AbstractNodeValidator;
use Stillat\BladeParser\Validation\ValidationResult;

class ForElseStructureValidator extends AbstractNodeValidator
{
    public bool $requiresStructures = true;

    public function validate(AbstractNode $node): ?ValidationResult
    {
        if (! $node instanceof DirectiveNode || $node->getForElse() == null) {
            return null;
        }
        $forElse = $node->getForElse();
        $emptyCount = $forElse->getEmptyDirectiveCount();

        if ($emptyCount == 1) {
            return null;
        }

        if ($emptyCount > 1) {
            return $this->makeValidationResult($node, 'Too many [@empty] directives inside [@forelse]');
        }

        return $this->makeValidationResult($node, 'Missing [@empty] directive inside [@forelse]');
    }
}
