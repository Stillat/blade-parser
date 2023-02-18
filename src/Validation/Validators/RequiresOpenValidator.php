<?php

namespace Stillat\BladeParser\Validation\Validators;

use Stillat\BladeParser\Compiler\CompilerServices\CoreDirectiveRetriever;
use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Validation\AbstractNodeValidator;
use Stillat\BladeParser\Validation\ValidationResult;
use Stillat\BladeParser\Validation\Validators\Concerns\AcceptsCustomDirectives;
use Stillat\BladeParser\Validation\Validators\Concerns\CanIgnoreDirectives;

class RequiresOpenValidator extends AbstractNodeValidator
{
    use AcceptsCustomDirectives, CanIgnoreDirectives;

    public bool $requiresStructures = true;

    protected array $coreDirectives = [];

    public function __construct()
    {
        $this->coreDirectives = CoreDirectiveRetriever::instance()->getDirectivesRequiringOpen();
    }

    public function validate(AbstractNode $node): ?ValidationResult
    {
        if (! $node instanceof DirectiveNode || $this->shouldIgnore($node)) {
            return null;
        }
        if (! $node->isClosingDirective || $node->isOpenedBy !== null) {
            return null;
        }

        if (in_array($node->content, $this->coreDirectives) || in_array($node->content, $this->customDirectives)) {
            return $this->makeValidationResult($node, "Missing required opening directive for [@{$node->content}]");
        }

        return null;
    }
}
