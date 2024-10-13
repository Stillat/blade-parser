<?php

namespace Stillat\BladeParser\Validation\Validators;

use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\EchoNode;
use Stillat\BladeParser\Nodes\PhpBlockNode;
use Stillat\BladeParser\Validation\AbstractNodeValidator;
use Stillat\BladeParser\Validation\PhpSyntaxValidationResult;
use Stillat\BladeParser\Validation\PhpSyntaxValidator;
use Stillat\BladeParser\Validation\ValidationResult;
use Stillat\BladeParser\Validation\Validators\Concerns\CanIgnoreDirectives;

class NodeCompilationValidator extends AbstractNodeValidator
{
    use CanIgnoreDirectives;

    public bool $requiresStructures = true;

    public function validate(AbstractNode $node): ?ValidationResult
    {
        if ($node instanceof DirectiveNode && $this->shouldIgnore($node)) {
            return null;
        }

        $syntaxValidator = new PhpSyntaxValidator;
        $result = new PhpSyntaxValidationResult;

        if ($node instanceof EchoNode) {
            $result = $syntaxValidator->checkString($node->content, $node->position->startLine);
        } elseif ($node instanceof PhpBlockNode) {
            $result = $syntaxValidator->checkString('<?php '.$node->innerContent.' ?>', $node->position->startLine);
        }
        $result->node = $node;

        return $result->toValidationResult();
    }
}
