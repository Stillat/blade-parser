<?php

namespace Stillat\BladeParser\Errors;

use Illuminate\Support\Str;
use Stillat\BladeParser\Nodes\Position;

class ErrorMessagePrinter
{
    public static function getErrorCode(BladeError $error): string
    {
        $context = Str::padLeft($error->context->value(), 3, '0');
        $type = Str::padLeft($error->type->value(), 3, '0');

        if ($error->type == ErrorType::ValidationError) {
            $type = '';
        }

        return 'BLADE_'.$error->family->label().$context.$type;
    }

    public static function getErrorString(BladeError $error): string
    {
        return '['.self::getErrorCode($error).'] '.self::getErrorTypeLabel($error->type).' '.
               self::getContextLabel($error->context).' '.
               self::getLineString($error->position, $error->shiftLine);
    }

    public static function getLineString(Position $position, int $shiftLine = 0): string
    {
        return 'on line '.$position->startLine + $shiftLine;
    }

    public static function getErrorTypeLabel(ErrorType $type): string
    {
        return match ($type) {
            ErrorType::UnexpectedEndOfInput => 'Unexpected end of input while parsing',
            ErrorType::UnexpectedRawEchoEncountered => 'Unexpected {!! while parsing',
            ErrorType::UnexpectedEchoEncountered => 'Unexpected {{ while parsing',
            ErrorType::UnexpectedCommentEncountered => 'Unexpected {{-- while parsing',
            ErrorType::UnexpectedTripleEchoEncountered => 'Unexpected {{{ while parsing',
            ErrorType::UnexpectedComponentTagEncountered => 'Unexpected <x- while parsing',
            ErrorType::UnexpectedNamespacedComponentTagEncountered => 'Unexpected <x: while parsing',
            ErrorType::UnexpectedComponentClosingTagEncountered => 'Unexpected </x- while parsing',
            ErrorType::UnexpectedNamespacedComponentClosingTagEncountered => 'Unexpected </x: while parsing',
            ErrorType::UnexpectedPhpShortOpen => 'Unexpected <?= while parsing',
            ErrorType::UnexpectedPhpOpen => 'Unexpected <?php while parsing',
            ErrorType::UnexpectedPhpClosingTag => 'Unexpected ?> while parsing',
            ErrorType::ValidationError => 'Validation failed',
            ErrorType::Unknown => '',
        };
    }

    public static function getContextLabel(ConstructContext $context): string
    {
        return match ($context) {
            ConstructContext::BladePhpBlock => '@php block',
            ConstructContext::Comment => 'comment',
            ConstructContext::Echo => 'echo',
            ConstructContext::ComponentTag => 'component',
            ConstructContext::DirectiveArguments => 'directive arguments',
            ConstructContext::Verbatim => 'verbatim',
            ConstructContext::TripleEcho => 'echo',
            ConstructContext::RawEcho => 'echo',
            ConstructContext::PhpOpen => '<?php tag',
            ConstructContext::PhpShortOpen => '<?= tag',
            ConstructContext::Condition => 'condition',
            ConstructContext::Literal => 'literal content',
            ConstructContext::Directive => 'directive',
        };
    }
}
