<?php

namespace Stillat\BladeParser\Validation\Validators;

use Illuminate\Support\Str;
use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Validation\AbstractNodeValidator;
use Stillat\BladeParser\Validation\ValidationResult;
use Stillat\BladeParser\Validation\Validators\Concerns\CanIgnoreDirectives;

class DirectiveArgumentsSpanningLinesValidator extends AbstractNodeValidator
{
    use CanIgnoreDirectives;

    protected int $maxLineSpan = 1;

    public function setMaxLineSpan(int $maxLineSpan): void
    {
        $this->maxLineSpan = $maxLineSpan;
    }

    public function validate(AbstractNode $node): ?ValidationResult
    {
        if (! $node instanceof DirectiveNode || $this->shouldIgnore($node) || ! $node->hasArguments()) {
            return null;
        }

        if ($node->getSpannedLineCount() > $this->maxLineSpan) {
            $userLine = Str::plural('line', $node->getSpannedLineCount());
            $line = Str::plural('line', $this->maxLineSpan);

            return $this->makeValidationResult($node, "Maximum line count exceeded for [@{$node->content}] arguments; found {$node->getSpannedLineCount()} {$userLine} expecting a maximum of {$this->maxLineSpan} {$line}");
        }

        return null;
    }
}
