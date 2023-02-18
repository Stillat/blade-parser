<?php

namespace Stillat\BladeParser\Errors;

use Stillat\BladeParser\Nodes\Position;

class BladeError
{
    public Position $position;

    public ErrorType $type;

    public ConstructContext $context;

    public ErrorFamily $family;

    public string $message = '';

    public int $shiftLine = 0;

    public function __construct(Position $position, ErrorType $type, ConstructContext $context, ErrorFamily $family = ErrorFamily::Parser)
    {
        $this->position = $position;
        $this->type = $type;
        $this->context = $context;
        $this->family = $family;
    }

    /**
     * Returns a formatted Blade error messages.
     *
     * Error messages will contain a generated error code, a message indicating the failure
     * as well as information such as the offending line number.
     */
    public function getErrorMessage(): string
    {
        if (mb_strlen($this->message) > 0) {
            return '['.self::getErrorCode().'] '.$this->message.' on line '.$this->position->startLine + $this->shiftLine;
        }

        return ErrorMessagePrinter::getErrorString($this);
    }

    /**
     * Returns a Blade error code representing the current error instance.
     */
    public function getErrorCode(): string
    {
        return ErrorMessagePrinter::getErrorCode($this);
    }

    /**
     * Tests if the current error is considered a "fatal" error.
     *
     * Fatal errors are those that are likely to cause issues when
     * compiling the final PHP for a given template. Some parser
     * generated errors are "warnings", and will not be reported
     * as fatal errors. These exceptions are:
     *
     * - PHP tags not having their final closing `?>` tag.
     * - PHP blocks not being matched to an `@endphp` directive.
     */
    public function isFatal(): bool
    {
        if ($this->context == ConstructContext::PhpShortOpen || $this->context == ConstructContext::PhpOpen) {
            if ($this->type == ErrorType::UnexpectedEndOfInput) {
                return false;
            }
        }

        if ($this->type == ErrorType::UnexpectedEndOfInput && $this->context == ConstructContext::BladePhpBlock) {
            return false;
        }

        return true;
    }
}
