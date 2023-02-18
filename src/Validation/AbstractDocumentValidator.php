<?php

namespace Stillat\BladeParser\Validation;

use Stillat\BladeParser\Document\Document;

abstract class AbstractDocumentValidator extends AbstractValidator
{
    /**
     * Analyzes the provided document and reports any applicable validation errors.
     *
     * Implementations may return a single `ValidationResult` instance if they
     * only need to report one result, an array of `ValidationResult` instances
     * if they wish to report multiple results at once, or may return `null`
     * if there are no results to report for the provided node.
     *
     * @param  Document  $document The document to analyze.
     */
    abstract public function validate(Document $document): ValidationResult|array|null;
}
