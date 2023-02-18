<?php

namespace Stillat\BladeParser\Validation\Validators\Concerns;

use Stillat\BladeParser\Nodes\DirectiveNode;

trait CanIgnoreDirectives
{
    /**
     * A list of directive names that should be ignored.
     *
     * @var string[]
     */
    protected array $ignoreDirectives = [];

    /**
     * Sets the list of directive names a validator instance should ignore.
     *
     * @param  string[]  $directives The directive names.
     */
    public function setIgnoreDirectives(array $directives): void
    {
        $this->ignoreDirectives = $directives;
    }

    /**
     * Tests if the provided directive should be ignored by the validator.
     *
     * @param  DirectiveNode  $directive The directive.
     */
    protected function shouldIgnore(DirectiveNode $directive): bool
    {
        return in_array($directive->content, $this->ignoreDirectives);
    }
}
