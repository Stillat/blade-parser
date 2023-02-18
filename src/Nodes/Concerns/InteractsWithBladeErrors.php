<?php

namespace Stillat\BladeParser\Nodes\Concerns;

use Stillat\BladeParser\Errors\BladeError;
use Stillat\BladeParser\Errors\ConstructContext;
use Stillat\BladeParser\Errors\ErrorType;

trait InteractsWithBladeErrors
{
    /**
     * Retrieves the first error.
     *
     * If the error source contains multiple types of errors, such as
     * parser errors and validation errors, all errors will be considered.
     */
    public function getFirstError(): ?BladeError
    {
        return $this->getErrors()->first();
    }

    /**
     * Retrieves the first fatal error.
     *
     * If the error source contains multiple types of errors, such as
     * parser errors and validation errors, all errors will be considered.
     * Fatal errors are considered those that would produce invalid
     * compiled PHP code, regardless of which compiler implementation is used.
     */
    public function getFirstFatalError(): ?BladeError
    {
        return $this->getErrors()->first(fn (BladeError $e) => $e->isFatal());
    }

    /**
     * Tests if any errors are present.
     */
    public function hasErrors(): bool
    {
        return $this->getErrors()->count() > 0;
    }

    /**
     * Tests if any fatal errors are present.
     */
    public function hasFatalErrors(): bool
    {
        foreach ($this->getErrors() as $error) {
            if ($error->isFatal()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Tests if an error matching the provided properties exists on a specific line.
     *
     * @param  int  $line The line to check.
     * @param  ErrorType  $type The error type to check for.
     * @param  ConstructContext  $context The error context.
     */
    public function hasErrorOnLine(int $line, ErrorType $type, ConstructContext $context): bool
    {
        $hasError = false;

        foreach ($this->getErrors() as $error) {
            if ($error->position->startLine == $line || $error->position->endLine == $line) {
                if ($error->type == $type && $error->context == $context) {
                    $hasError = true;
                    break;
                }
            }
        }

        return $hasError;
    }
}
