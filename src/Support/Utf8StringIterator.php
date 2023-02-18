<?php

namespace Stillat\BladeParser\Support;

use ArrayAccess;
use Countable;
use Iterator;

class Utf8StringIterator implements ArrayAccess, Countable, Iterator
{
    protected string $string = '';

    protected int $position = 0;

    protected int $lastSize = 1;

    protected int $length = 0;

    protected ?array $stringIndex = null;

    public function __construct(string $string)
    {
        $this->string = $string;
        $this->length = mb_strlen($string, 'UTF-8');
    }

    public function setIteratorPosition(
        int $position,
        int $lastSize): void
    {
        $this->position = $position;
        $this->lastSize = $lastSize;
    }

    public function count(): int
    {
        return $this->length;
    }

    public function index(): array
    {
        $this->buildStringIndex();

        return $this->stringIndex;
    }

    public function current(): string
    {
        $firstByte = ord($this->string[$this->position]);

        $this->lastSize = match (true) {
            $firstByte <= 127 => 1,
            $firstByte <= 223 => 2,
            $firstByte <= 239 => 3,
            default => 4
        };

        return substr(
            $this->string,
            $this->position,
            $this->lastSize
        );
    }

    public function next(): void
    {
        $this->position += $this->lastSize;
    }

    public function key(): int
    {
        return $this->position;
    }

    public function valid(): bool
    {
        return isset($this->string[$this->position]);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function lastCharSize(): int
    {
        return $this->lastSize;
    }

    private function buildStringIndex(): void
    {
        if ($this->stringIndex !== null) {
            return;
        }

        $this->stringIndex = [];
        $string = new Utf8StringIterator($this->string);

        $charIndex = 0;

        foreach ($string as $char) {
            $this->stringIndex[$charIndex] = $string->lastCharSize();
            $charIndex += 1;
        }

        unset($string);
    }

    public function offsetExists(mixed $offset): bool
    {
        $this->buildStringIndex();

        return array_key_exists(
            $offset,
            $this->stringIndex
        );
    }

    public function offsetGet(mixed $offset): string
    {
        $this->buildStringIndex();

        return substr(
            $this->string,
            $this->getPosition($offset),
            $this->stringIndex[$offset]
        );
    }

    public function getPosition($offset): int
    {
        $position = 0;

        for ($i = 0; $i < $offset; $i++) {
            $position += $this->stringIndex[$i];
        }

        return $position;
    }

    public function getPositionFromCharIndex($charIndex): int
    {
        $this->buildStringIndex();

        return $this->getPosition($charIndex);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->buildStringIndex();

        $this->string = substr_replace(
            $this->string,
            $value,
            $this->getPosition($offset),
            $this->stringIndex[$offset]
        );
        $this->stringIndex = null;

        $this->buildStringIndex();
        $this->updateStringMetaData();
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->buildStringIndex();

        $this->string = substr_replace(
            $this->string,
            '',
            $this->getPosition($offset),
            $this->stringIndex[$offset]
        );
        unset($this->stringIndex[$offset]);

        $this->updateStringMetaData();
    }

    protected function updateStringMetaData(): void
    {
        $this->stringIndex = array_values($this->stringIndex);
        $this->length = mb_strlen($this->string);
    }

    public function __toString(): string
    {
        return $this->string;
    }
}
