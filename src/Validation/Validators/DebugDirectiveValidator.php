<?php

namespace Stillat\BladeParser\Validation\Validators;

use Stillat\BladeParser\Compiler\CompilerServices\CoreDirectiveRetriever;
use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Validation\AbstractNodeValidator;
use Stillat\BladeParser\Validation\ValidationResult;
use Stillat\BladeParser\Validation\Validators\Concerns\AcceptsCustomDirectives;
use Stillat\BladeParser\Validation\Validators\Concerns\CanIgnoreDirectives;

class DebugDirectiveValidator extends AbstractNodeValidator
{
    use AcceptsCustomDirectives, CanIgnoreDirectives;

    protected array $coreDebugDirectives = [];

    public function __construct()
    {
        $this->coreDebugDirectives = CoreDirectiveRetriever::instance()->getDebugDirectiveNames();
    }

    /**
     * Tests if the node is a directive node, and is considered a "debug" directive.
     *
     * @param  AbstractNode  $node  The node to validate.
     */
    public function validate(AbstractNode $node): ValidationResult|array|null
    {
        if (! $node instanceof DirectiveNode || $this->shouldIgnore($node)) {
            return null;
        }

        if (in_array($node->content, $this->coreDebugDirectives) || in_array($node->content, $this->customDirectives)) {
            return $this->makeValidationResult($node, "Debug directive [@{$node->content}] detected");
        }

        return null;
    }
}
