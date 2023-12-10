<?php

namespace Stillat\BladeParser\Validation\Validators;

use Stillat\BladeParser\Compiler\CompilerServices\CoreDirectiveRetriever;
use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Validation\AbstractNodeValidator;
use Stillat\BladeParser\Validation\ValidationResult;
use Stillat\BladeParser\Validation\Validators\Concerns\AcceptsCustomDirectives;
use Stillat\BladeParser\Validation\Validators\Concerns\CanIgnoreDirectives;

class RequiredArgumentsValidator extends AbstractNodeValidator
{
    use AcceptsCustomDirectives, CanIgnoreDirectives;

    /**
     * @var string[]
     */
    protected array $coreDirectives = [];

    public function __construct()
    {
        $this->coreDirectives = CoreDirectiveRetriever::instance()->getDirectivesRequiringArguments();
    }

    public function validate(AbstractNode $node): ?ValidationResult
    {
        if (! $node instanceof DirectiveNode || $this->shouldIgnore($node) || $node->getIsConditionDirective() || mb_strlen($this->getDirectiveArgContents($node)) > 0) {
            return null;
        }

        if (in_array($node->content, $this->coreDirectives) || in_array($node->content, $this->customDirectives)) {
            return $this->makeValidationResult($node, "Required arguments missing for [@{$node->content}]");
        }

        return null;
    }
}
