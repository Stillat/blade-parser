<?php

namespace Stillat\BladeParser\Validation;

use Stillat\BladeParser\Errors\BladeError;
use Stillat\BladeParser\Errors\ConstructContext;
use Stillat\BladeParser\Errors\ErrorFamily;
use Stillat\BladeParser\Errors\ErrorType;
use Stillat\BladeParser\Nodes\AbstractNode;

class ValidationResult
{
    /**
     * The node that triggered the validation error.
     */
    public AbstractNode $subject;

    /**
     * The validation error message.
     */
    public string $message = '';

    /**
     * The "shift" line.
     *
     * Shift line values can be used to change the reported line number.
     * An example might be a PHP syntax error on "line 5" within a
     * `@php @endphp` block. However, that block may have started on
     * line 10 in the original document. We can use line shifts
     * to report the correct line of 15 to the end user.
     */
    public int $shiftLine = 0;

    /**
     * The `ErrorFamily` for the current validation error.
     */
    public ?ErrorFamily $errorFamily = null;

    /**
     * An optional validator class name that is used to help identify results internally.
     *
     * @internal
     */
    public string $createdFromValidatorClass = '';

    public function __construct(AbstractNode $subject, string $message = '')
    {
        $this->subject = $subject;
        $this->message = $message;
    }

    /**
     * Converts the ValidationResult instance to a "slug" that
     * can be used to help easily identify duplicate results.
     *
     * @internal
     */
    public function toSlug(): string
    {
        return $this->createdFromValidatorClass.$this->subject->index.'_'.$this->subject->id.'_'.$this->shiftLine.$this->errorFamily?->label();
    }

    /**
     * Converts the ValidationResult instance to an instance of BladeError.
     */
    public function toBladeError(): BladeError
    {
        $error = new BladeError($this->subject->position, ErrorType::ValidationError, ConstructContext::fromNode($this->subject), ErrorFamily::Validation);
        $error->message = $this->message;
        $error->shiftLine = $this->shiftLine;

        if ($this->errorFamily != null) {
            $error->family = $this->errorFamily;
        }

        return $error;
    }
}
