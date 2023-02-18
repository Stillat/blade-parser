<?php

namespace Stillat\BladeParser\Errors\Exceptions;

use Exception;
use Stillat\BladeParser\Errors\BladeError;
use Stillat\BladeParser\Errors\ErrorMessagePrinter;

class CompilationException extends Exception
{
    public ?BladeError $error;

    public static function fromParserError(BladeError $error): CompilationException
    {
        $message = ErrorMessagePrinter::getErrorString($error);

        $compilationException = new CompilationException($message);
        $compilationException->error = $error;

        return $compilationException;
    }
}
