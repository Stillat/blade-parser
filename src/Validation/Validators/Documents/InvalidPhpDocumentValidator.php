<?php

namespace Stillat\BladeParser\Validation\Validators\Documents;

use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Validation\AbstractDocumentValidator;
use Stillat\BladeParser\Validation\PhpSyntaxValidator;
use Stillat\BladeParser\Validation\ValidationResult;

class InvalidPhpDocumentValidator extends AbstractDocumentValidator
{
    /**
     * Attempts to detect any invalid PHP syntax in the provided document.
     *
     * @param  Document  $document  The document instance.
     */
    public function validate(Document $document): ?ValidationResult
    {
        $syntaxValidator = new PhpSyntaxValidator();
        $result = $syntaxValidator->checkDocument($document);
        if (! $result->detectedErrors) {
            return null;
        }

        return $result->toValidationResult();
    }
}
