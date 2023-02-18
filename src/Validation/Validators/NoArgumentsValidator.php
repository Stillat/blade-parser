<?php

namespace Stillat\BladeParser\Validation\Validators;

use Stillat\BladeParser\Compiler\CompilerServices\CoreDirectiveRetriever;
use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Validation\AbstractNodeValidator;
use Stillat\BladeParser\Validation\ValidationResult;
use Stillat\BladeParser\Validation\Validators\Concerns\AcceptsCustomDirectives;
use Stillat\BladeParser\Validation\Validators\Concerns\CanIgnoreDirectives;

class NoArgumentsValidator extends AbstractNodeValidator
{
    use CanIgnoreDirectives, AcceptsCustomDirectives;

    /**
     * @var string[]
     */
    protected array $coreDirectives = [];

    public function __construct()
    {
        $this->coreDirectives = CoreDirectiveRetriever::instance()->getDirectivesThatMustNotHaveArguments();
    }

    public function validate(AbstractNode $node): ?ValidationResult
    {
        if (! $node instanceof DirectiveNode || ! $node->hasArguments() || $this->shouldIgnore($node)) {
            return null;
        }

        if (in_array($node->content, $this->coreDirectives) || in_array($node->content, $this->customDirectives)) {
            return $this->makeValidationResult($node, "[@{$node->content}] should not have any arguments");
        }

        return null;
    }
}
