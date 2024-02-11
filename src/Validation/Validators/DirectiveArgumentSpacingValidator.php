<?php

namespace Stillat\BladeParser\Validation\Validators;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Validation\AbstractNodeValidator;
use Stillat\BladeParser\Validation\ValidationResult;
use Stillat\BladeParser\Validation\Validators\Concerns\CanIgnoreDirectives;

class DirectiveArgumentSpacingValidator extends AbstractNodeValidator
{
    use CanIgnoreDirectives;

    /**
     * The number of expected spaces between directives and arguments.
     */
    protected int $expectedSpacing = 1;

    /**
     * Sets the number of expected spaces between directives and arguments.
     *
     * @param  int  $spaces  The required spaces.
     */
    public function setExpectedSpacing(int $spaces): void
    {
        if ($spaces < 0) {
            throw new InvalidArgumentException("The number of spaces must be greater than or equal to zero: [{$spaces}] provided.");
        }

        $this->expectedSpacing = $spaces;
    }

    public function validate(AbstractNode $node): ?ValidationResult
    {
        if (! $node instanceof DirectiveNode || ! $node->hasArguments() || $this->shouldIgnore($node)) {
            return null;
        }
        if ($node->getArgumentsDistance() == $this->expectedSpacing) {
            return null;
        }

        $spaces = Str::plural('space', $this->expectedSpacing);

        return $this->makeValidationResult($node, "Expected {$this->expectedSpacing} {$spaces} after [@{$node->content}], but found {$node->getArgumentsDistance()}");
    }
}
