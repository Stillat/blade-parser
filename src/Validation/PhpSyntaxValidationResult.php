<?php

namespace Stillat\BladeParser\Validation;

use Stillat\BladeParser\Errors\ErrorFamily;
use Stillat\BladeParser\Nodes\AbstractNode;

class PhpSyntaxValidationResult
{
    /**
     * Indicates if any errors were detected.
     */
    public bool $detectedErrors = false;

    /**
     * The original node errors were detected on.
     */
    public ?AbstractNode $node = null;

    /**
     * The reported PHP error line number.
     */
    public int $errorLine = 0;

    /**
     * The reported PHP error message.
     */
    public string $errorMessage = '';

    public function toValidationResult(): ?ValidationResult
    {
        if (! $this->detectedErrors || $this->node == null) {
            return null;
        }

        $result = new ValidationResult($this->node, $this->errorMessage);
        $result->createdFromValidatorClass = __CLASS__;
        $result->errorFamily = ErrorFamily::Compiler;

        // Subtract one to account for the line the node was already on.
        $result->shiftLine = $this->errorLine - 1;

        return $result;
    }
}
