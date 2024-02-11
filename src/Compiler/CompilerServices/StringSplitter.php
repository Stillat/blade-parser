<?php

namespace Stillat\BladeParser\Compiler\CompilerServices;

use Stillat\BladeParser\Parser\AbstractParser;

class StringSplitter extends AbstractParser
{
    /**
     * Resets the intermediate state of the parser.
     */
    private function resetState(): void
    {
        $this->content = '';
        $this->chars = [];
        $this->currentContent = [];
    }

    private function isBreakableWhitespace(?string $char): bool
    {
        if ($char == null) {
            return false;
        }

        if ($char == AbstractParser::C_NewLine) {
            return false;
        }

        return ctype_space($char);
    }

    /**
     * Splits a string on whitespace into an array, ignoring line breaks and embedded strings.
     *
     * @param  string  $input  The string to split.
     * @return string[]
     */
    public function split(string $input): array
    {
        $this->resetState();

        $this->content = $input;
        $this->inputLen = mb_strlen($this->content);
        $this->chunkSize = $this->inputLen;
        $this->prepareParseAt(0);

        $results = [];

        for ($this->currentIndex; $this->currentIndex < $this->inputLen; $this->currentIndex++) {
            $this->checkCurrentOffsets();

            if ($this->isStartingString()) {
                $this->skipToEndOfString();

                if ($this->isBreakableWhitespace($this->next) || $this->next == null) {
                    if (count($this->currentContent) > 0) {
                        $results[] = implode('', $this->currentContent);
                        $this->currentContent = [];

                        continue;
                    }
                }

                continue;
            }

            if ($this->isBreakableWhitespace($this->next) || $this->next == null) {
                if (count($this->currentContent) > 0) {
                    $this->currentContent[] = $this->cur;
                    $results[] = implode('', $this->currentContent);
                    $this->currentContent = [];

                    continue;
                }
            }

            if ($this->isBreakableWhitespace($this->cur)) {
                continue;
            }

            $this->currentContent[] = $this->cur;
        }

        return $results;
    }
}
