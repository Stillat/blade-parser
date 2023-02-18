<?php

namespace Stillat\BladeParser\Errors;

enum ErrorType
{
    case Unknown;
    case UnexpectedEndOfInput;
    case UnexpectedEchoEncountered;
    case UnexpectedRawEchoEncountered;
    case UnexpectedTripleEchoEncountered;
    case UnexpectedCommentEncountered;
    case UnexpectedComponentTagEncountered;
    case UnexpectedNamespacedComponentTagEncountered;
    case UnexpectedComponentClosingTagEncountered;
    case UnexpectedNamespacedComponentClosingTagEncountered;
    case UnexpectedPhpShortOpen;
    case UnexpectedPhpOpen;
    case UnexpectedPhpClosingTag;
    case ValidationError;

    public function value(): int
    {
        return match ($this) {
            self::Unknown => 0,
            self::UnexpectedEndOfInput => 1,
            self::UnexpectedEchoEncountered => 2,
            self::UnexpectedRawEchoEncountered => 3,
            self::UnexpectedTripleEchoEncountered => 4,
            self::UnexpectedCommentEncountered => 5,
            self::UnexpectedComponentTagEncountered => 6,
            self::UnexpectedNamespacedComponentTagEncountered => 7,
            self::UnexpectedComponentClosingTagEncountered => 8,
            self::UnexpectedNamespacedComponentClosingTagEncountered => 9,
            self::UnexpectedPhpShortOpen => 10,
            self::UnexpectedPhpOpen => 11,
            self::UnexpectedPhpClosingTag => 12,
            self::ValidationError => 13,
        };
    }
}
