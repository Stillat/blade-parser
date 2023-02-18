<?php

namespace Stillat\BladeParser\Parser;

use Stillat\BladeParser\Errors\ErrorType;
use Stillat\BladeParser\Nodes\Position;

abstract class AbstractParser
{
    const C_AtChar = '@';

    const C_ExclamationMark = '!';

    const C_Minus = '-';

    const C_Equals = '=';

    const C_Asterisk = '*';

    const C_ForwardSlash = '/';

    const C_DoubleQuote = '"';

    const C_SingleQuote = "'";

    const C_LeftBracket = '(';

    const C_RightBracket = ')';

    const C_LeftCurlyBracket = '{';

    const C_RightCurlyBracket = '}';

    const C_QuestionMark = '?';

    const C_LeftAngleBracket = '<';

    const C_RightAngleBracket = '>';

    const C_LeftSquareBracket = '[';

    const C_RightSquareBracket = ']';

    const C_EscapeCharacter = '\\';

    const C_NewLine = "\n";

    protected string $content = '';

    protected string $originalContent = '';

    protected int $inputLen = 0;

    protected array $documentOffsets = [];

    protected int $seedStartLine = 1;

    protected array $customDirectives = [];

    protected int $startIndex = 0;

    protected int $startLocation = 0;

    protected int $currentIndex = 0;

    protected array $currentContent = [];

    protected array $chars = [];

    protected int $charLen = 0;

    protected ?string $cur = null;

    protected ?string $next = null;

    protected ?string $prev = null;

    protected int $chunkSize = 5;

    protected bool $isParsingString = false;

    protected string $stringTerminator = '';

    protected int $currentChunkOffset = 0;

    protected function clearParserState(): void
    {
        $this->startIndex = 0;
        $this->startLocation = 0;
        $this->currentIndex = 0;
        $this->currentContent = [];
        $this->chars = [];
        $this->cur = null;
        $this->next = null;
        $this->prev = null;
        $this->isParsingString = false;
        $this->stringTerminator = '';
        $this->currentChunkOffset = 0;
    }

    public function getParsedContent(): string
    {
        return $this->content;
    }

    public function getOriginalContent(): string
    {
        return $this->originalContent;
    }

    protected function makePosition(int $startOffset, int $endOffset): Position
    {
        $position = new Position();
        $position->startOffset = $startOffset;
        $position->endOffset = $endOffset;

        return $position;
    }

    protected function fetchAt($location, $count): string
    {
        return mb_substr($this->content, $location, $count);
    }

    protected function fetchAtRelative($location, $count): string
    {
        return $this->fetchAt($location + $this->startLocation, $count);
    }

    protected function checkCurrentOffsets(): void
    {
        if (! array_key_exists($this->currentIndex, $this->chars)) {
            $this->cur = null;
            $this->prev = null;
            $this->next = null;

            return;
        }

        $this->cur = $this->chars[$this->currentIndex];

        $this->prev = null;
        $this->next = null;

        if ($this->currentIndex > 0) {
            $this->prev = $this->chars[$this->currentIndex - 1];
        }

        if (($this->currentIndex + 1) < $this->inputLen) {
            $doPeek = true;
            if ($this->currentIndex == $this->charLen - 1) {
                $nextChunk = mb_str_split(mb_substr($this->content, $this->currentChunkOffset + $this->chunkSize, $this->chunkSize));
                $this->currentChunkOffset += $this->chunkSize;

                if ($this->currentChunkOffset == $this->inputLen) {
                    $doPeek = false;
                }

                foreach ($nextChunk as $nextChar) {
                    $this->chars[] = $nextChar;
                    $this->charLen += 1;
                }
            }

            if ($doPeek && array_key_exists($this->currentIndex + 1, $this->chars)) {
                $this->next = $this->chars[$this->currentIndex + 1];
            }
        }
    }

    protected function advance($times = 1): void
    {
        for ($i = 0; $i < $times; $i++) {
            $this->currentIndex += 1;
            $this->checkCurrentOffsets();
            $this->currentContent[] = $this->cur;
        }
    }

    protected function prepareParseAt($location): void
    {
        $this->startLocation = $location;
        $this->currentContent = [];
        $this->startIndex = $location;
        $this->currentIndex = 0;
        $this->currentChunkOffset = $location;
        $this->chars = mb_str_split(mb_substr($this->content, $this->currentChunkOffset, $this->chunkSize));
        $this->charLen = count($this->chars);
    }

    protected function isStartingString(): bool
    {
        if ($this->isParsingString) {
            return false;
        }

        if ($this->cur == self::C_DoubleQuote || $this->cur == self::C_SingleQuote) {
            $this->stringTerminator = $this->cur;

            return true;
        }

        return false;
    }

    protected function isStartingLineComment(): bool
    {
        if ($this->cur == self::C_ForwardSlash && $this->next == self::C_ForwardSlash) {
            return true;
        }

        return false;
    }

    protected function isStartingMultilinePhpComment(): bool
    {
        if ($this->cur == self::C_ForwardSlash && $this->next == self::C_Asterisk) {
            return true;
        }

        return false;
    }

    protected function abandonParse(ErrorType $reason): ScanResult
    {
        $result = new ScanResult();
        $result->abandonReason = $reason;
        $result->didAbandon = true;
        $result->abandonedOffset = $this->startLocation + $this->currentIndex;

        return $result;
    }

    protected function checkCurrentPositionForStructures(bool $onlyComponents = false): ?ScanResult
    {
        if ($this->currentIndex > 3 && $this->cur == self::C_LeftAngleBracket && $this->next == 'x') {
            $checkContent = mb_strtolower($this->fetchAtRelative($this->currentIndex, 3));

            if ($checkContent == '<x-') {
                return $this->abandonParse(ErrorType::UnexpectedComponentTagEncountered);
            } elseif ($checkContent == '<x:') {
                return $this->abandonParse(ErrorType::UnexpectedNamespacedComponentTagEncountered);
            }
        }

        if ($this->currentIndex > 3 && $this->cur == self::C_LeftAngleBracket && $this->next == self::C_ForwardSlash) {
            $checkContent = mb_strtolower($this->fetchAtRelative($this->currentIndex, 4));

            if ($checkContent == '</x-') {
                return $this->abandonParse(ErrorType::UnexpectedComponentClosingTagEncountered);
            } elseif ($checkContent == '</x:') {
                return $this->abandonParse(ErrorType::UnexpectedNamespacedComponentClosingTagEncountered);
            }
        }

        if ($onlyComponents) {
            return null;
        }

        if ($this->currentIndex > 2 && $this->cur == self::C_LeftAngleBracket && $this->next == self::C_QuestionMark) {
            $phpCheck = $this->fetchAtRelative($this->currentIndex, 3);

            if ($phpCheck == '<?=') {
                return $this->abandonParse(ErrorType::UnexpectedPhpShortOpen);
            }
        }

        if ($this->currentIndex > 2 && $this->cur == self::C_QuestionMark && $this->next == self::C_RightAngleBracket) {
            return $this->abandonParse(ErrorType::UnexpectedPhpClosingTag);
        }

        if ($this->currentIndex > 3 && $this->cur == self::C_LeftCurlyBracket && $this->next == self::C_LeftCurlyBracket && $this->prev != self::C_AtChar) {
            $checkContent = $this->fetchAtRelative($this->currentIndex, 4);

            if ($checkContent == '{{--') {
                return $this->abandonParse(ErrorType::UnexpectedCommentEncountered);
            }
        }

        if ($this->currentIndex > 3 && $this->prev != self::C_AtChar && $this->cur == self::C_LeftCurlyBracket && $this->next == self::C_LeftCurlyBracket && $this->fetchAtRelative($this->currentIndex + 2, 1) == self::C_LeftCurlyBracket) {
            return $this->abandonParse(ErrorType::UnexpectedTripleEchoEncountered);
        }

        if ($this->currentIndex > 3 && $this->prev != self::C_AtChar && $this->cur == self::C_LeftCurlyBracket && $this->next == self::C_ExclamationMark && $this->fetchAtRelative($this->currentIndex + 2, 1) == self::C_ExclamationMark) {
            return $this->abandonParse(ErrorType::UnexpectedRawEchoEncountered);
        }

        if ($this->currentIndex > 2 && $this->cur == self::C_LeftCurlyBracket && $this->next == self::C_LeftCurlyBracket && $this->prev != self::C_AtChar) {
            return $this->abandonParse(ErrorType::UnexpectedEchoEncountered);
        }

        return null;
    }

    protected function skipToEndOfMultilinePhpComment(): void
    {
        for ($this->currentIndex; $this->currentIndex < $this->inputLen; $this->currentIndex++) {
            $this->checkCurrentOffsets();

            $this->currentContent[] = $this->cur;

            if ($this->cur == self::C_Asterisk && $this->next == self::C_ForwardSlash) {
                $this->advance();
                break;
            }
        }
    }

    protected function skipToEndOfLine(): void
    {
        for ($this->currentIndex; $this->currentIndex < $this->inputLen; $this->currentIndex++) {
            $this->checkCurrentOffsets();

            $this->currentContent[] = $this->cur;

            if ($this->cur == self::C_NewLine) {
                break;
            }
        }
    }

    protected function skipToEndOfString(): void
    {
        $this->isParsingString = true;

        $this->currentContent[] = $this->cur;
        $this->currentIndex += 1;

        for ($this->currentIndex; $this->currentIndex < $this->inputLen; $this->currentIndex++) {
            $this->checkCurrentOffsets();

            $this->currentContent[] = $this->cur;

            if ($this->cur == self::C_EscapeCharacter && $this->prev == self::C_EscapeCharacter && $this->next == $this->stringTerminator) {
                $this->advance();
                $this->isParsingString = false;
                break;
            } elseif ($this->cur == $this->stringTerminator && $this->prev != self::C_EscapeCharacter) {
                $this->isParsingString = false;
                break;
            }
        }

        $this->isParsingString = false;
    }

    protected function seekToEndOfTripleEcho(): void
    {
        for ($this->currentIndex; $this->currentIndex < $this->inputLen; $this->currentIndex++) {
            $this->checkCurrentOffsets();

            if ($this->isStartingString()) {
                $this->skipToEndOfString();

                continue;
            }

            $this->currentContent[] = $this->cur;

            if ($this->cur == self::C_RightCurlyBracket && $this->prev == self::C_RightCurlyBracket && $this->next == self::C_RightCurlyBracket) {
                $this->advance();

                break;
            }
        }
    }

    protected function seekToEndOfRawEcho(): void
    {
        for ($this->currentIndex; $this->currentIndex < $this->inputLen; $this->currentIndex++) {
            $this->checkCurrentOffsets();

            if ($this->isStartingString()) {
                $this->skipToEndOfString();

                continue;
            }

            $this->currentContent[] = $this->cur;

            if ($this->cur == self::C_ExclamationMark && $this->prev == self::C_ExclamationMark && $this->next == self::C_RightCurlyBracket) {
                $this->advance();
                break;
            }
        }
    }

    protected function seekEndOfEcho(): void
    {
        for ($this->currentIndex; $this->currentIndex < $this->inputLen; $this->currentIndex++) {
            $this->checkCurrentOffsets();

            if ($this->isStartingString()) {
                $this->skipToEndOfString();

                continue;
            }

            $this->currentContent[] = $this->cur;

            if ($this->cur == self::C_RightCurlyBracket && $this->prev == self::C_RightCurlyBracket) {
                break;
            }
        }
    }

    protected function scanToEndOfComponentTag($location): ?ScanResult
    {
        $this->prepareParseAt($location);

        for ($this->currentIndex; $this->currentIndex < $this->inputLen; $this->currentIndex++) {
            $this->checkCurrentOffsets();

            if ($this->cur == self::C_LeftCurlyBracket && $this->next == self::C_LeftCurlyBracket) {
                $this->seekEndOfEcho();

                continue;
            }

            if ($this->isStartingString()) {
                $this->skipToEndOfString();

                continue;
            }

            if ($structureError = $this->checkCurrentPositionForStructures(true)) {
                return $structureError;
            }

            $this->currentContent[] = $this->cur;

            if ($this->cur == self::C_ForwardSlash && $this->next == self::C_RightAngleBracket) {
                $this->advance();

                $scanResult = new ScanResult();
                $scanResult->offset = $location;
                $scanResult->content = implode('', $this->currentContent);

                return $scanResult;
            }

            if ($this->cur == self::C_RightAngleBracket) {
                $scanResult = new ScanResult();
                $scanResult->offset = $location;
                $scanResult->content = implode('', $this->currentContent);

                return $scanResult;
            }
        }

        return null;
    }

    protected function scanToEndOfComment($location): ?ScanResult
    {
        $this->prepareParseAt($location);

        for ($this->currentIndex; $this->currentIndex < $this->inputLen; $this->currentIndex++) {
            $this->checkCurrentOffsets();

            $this->currentContent[] = $this->cur;

            if ($this->cur == self::C_Minus &&
                $this->prev == self::C_Minus &&
                $this->next == self::C_RightCurlyBracket &&
                $this->fetchAt($this->currentIndex + $this->startIndex, 3)) {
                $this->advance();
                $this->advance();

                $scanResult = new ScanResult();
                $scanResult->offset = $location;
                $scanResult->content = implode('', $this->currentContent);

                return $scanResult;
            }
        }

        return null;
    }

    protected function scanToEndOfPhp($location): ?ScanResult
    {
        $this->prepareParseAt($location);

        for ($this->currentIndex; $this->currentIndex < $this->inputLen; $this->currentIndex++) {
            $this->checkCurrentOffsets();

            if ($this->isStartingString()) {
                $this->skipToEndOfString();

                continue;
            }

            $this->currentContent[] = $this->cur;

            if ($this->cur == self::C_RightAngleBracket && $this->prev == self::C_QuestionMark) {
                $scanResult = new ScanResult();
                $scanResult->offset = $location;
                $scanResult->content = implode('', $this->currentContent);

                return $scanResult;
            }
        }

        return null;
    }

    protected function scanToEndOfEcho($location): ?ScanResult
    {
        $this->prepareParseAt($location);

        for ($this->currentIndex; $this->currentIndex < $this->inputLen; $this->currentIndex++) {
            $this->checkCurrentOffsets();

            if ($this->isStartingString()) {
                $this->skipToEndOfString();

                continue;
            }

            if ($structureError = $this->checkCurrentPositionForStructures()) {
                return $structureError;
            }

            $this->currentContent[] = $this->cur;

            if ($this->cur == self::C_RightCurlyBracket && $this->prev == self::C_RightCurlyBracket) {
                $scanResult = new ScanResult();
                $scanResult->offset = $location;
                $scanResult->content = implode('', $this->currentContent);

                return $scanResult;
            }
        }

        return null;
    }

    protected function scanToEndOfTripleEcho($location): ?ScanResult
    {
        $this->prepareParseAt($location);

        for ($this->currentIndex; $this->currentIndex < $this->inputLen; $this->currentIndex++) {
            $this->checkCurrentOffsets();

            if ($this->isStartingString()) {
                $this->skipToEndOfString();

                continue;
            }

            if ($structureError = $this->checkCurrentPositionForStructures()) {
                return $structureError;
            }

            $this->currentContent[] = $this->cur;

            if ($this->cur == self::C_RightCurlyBracket && $this->prev == self::C_RightCurlyBracket && $this->next == self::C_RightCurlyBracket) {
                $this->advance();

                $scanResult = new ScanResult();
                $scanResult->offset = $location;
                $scanResult->content = implode('', $this->currentContent);

                return $scanResult;
            }
        }

        return null;
    }

    protected function scanToEndOfRawEcho($location): ?ScanResult
    {
        $this->prepareParseAt($location);

        for ($this->currentIndex; $this->currentIndex < $this->inputLen; $this->currentIndex++) {
            $this->checkCurrentOffsets();

            if ($this->isStartingString()) {
                $this->skipToEndOfString();

                continue;
            }

            if ($structureError = $this->checkCurrentPositionForStructures()) {
                return $structureError;
            }

            $this->currentContent[] = $this->cur;

            if ($this->cur == self::C_ExclamationMark && $this->prev == self::C_ExclamationMark && $this->next == self::C_RightCurlyBracket) {
                $this->advance();

                $scanResult = new ScanResult();
                $scanResult->offset = $location;
                $scanResult->content = implode('', $this->currentContent);

                return $scanResult;
            }
        }

        return null;
    }

    protected function scanToEndOfArgumentGroup($location): ?ScanResult
    {
        $this->prepareParseAt($location);

        $currentStack = 0;

        for ($this->currentIndex = 0; $this->currentIndex < $this->inputLen; $this->currentIndex++) {
            $this->checkCurrentOffsets();

            if ($this->isStartingString()) {
                $this->skipToEndOfString();

                continue;
            }

            if ($this->isStartingLineComment()) {
                $this->skipToEndOfLine();

                continue;
            }

            if ($this->isStartingMultilinePhpComment()) {
                $this->skipToEndOfMultilinePhpComment();

                continue;
            }

            $this->currentContent[] = $this->cur;

            if ($this->cur == self::C_LeftBracket) {
                $currentStack += 1;

                continue;
            } elseif ($this->cur == self::C_RightBracket) {
                $currentStack -= 1;

                if ($currentStack <= 0) {
                    $result = new ScanResult();
                    $result->content = implode('', $this->currentContent);
                    $result->offset = $location;

                    return $result;
                }

                continue;
            }
        }

        return null;
    }

    protected function peekNextNonWhitespaceAt($location): ?ScanResult
    {
        for ($i = $location; $i < $this->inputLen; $i++) {
            $cur = $this->chars[$i];

            if (ctype_space($cur)) {
                continue;
            }

            $result = new ScanResult();
            $result->content = $cur;
            $result->offset = $this->startIndex + $i;

            return $result;
        }

        return null;
    }

    protected function fetchNextNonWhitespaceAt($location): ?ScanResult
    {
        $this->prepareParseAt($location);

        for ($this->currentIndex = 0; $this->currentIndex < $this->inputLen; $this->currentIndex++) {
            $this->checkCurrentOffsets();

            if ($this->cur == null) {
                break;
            }

            if (ctype_space($this->cur)) {
                continue;
            }

            $result = new ScanResult();
            $result->content = $this->cur;
            $result->offset = $this->startIndex + $this->currentIndex;

            return $result;
        }

        return null;
    }

    protected function fetchDirectiveNameAt($location): string
    {
        $this->prepareParseAt($location);

        for ($this->currentIndex = 0; $this->currentIndex < $this->inputLen; $this->currentIndex++) {
            $this->checkCurrentOffsets();

            $this->currentContent[] = $this->cur;

            if ($this->next == null || $this->next == self::C_NewLine || (! ctype_alpha($this->next) && $this->next != '_' && $this->next != ':')) {
                break;
            }
        }

        return implode('', $this->currentContent);
    }

    protected function fetchAlphaNumericAt($location): string
    {
        $this->prepareParseAt($location);

        for ($this->currentIndex = 0; $this->currentIndex < $this->inputLen; $this->currentIndex++) {
            $this->checkCurrentOffsets();

            $this->currentContent[] = $this->cur;

            if ($this->next == null || $this->next == self::C_NewLine || ! ctype_alpha($this->next)) {
                break;
            }
        }

        return implode('', $this->currentContent);
    }
}
