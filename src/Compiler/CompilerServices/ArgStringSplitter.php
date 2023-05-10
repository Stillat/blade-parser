<?php

namespace Stillat\BladeParser\Compiler\CompilerServices;

use Stillat\BladeParser\Parser\AbstractParser;

class ArgStringSplitter extends AbstractParser
{
    private function resetState(): void
    {
        $this->content = '';
        $this->chars = [];
        $this->currentContent = [];
    }

    protected function skipToEndOfPairedStructure(string $structureStart, string $structureEnd)
    {
        $structureCount = 0;

        for ($this->currentIndex; $this->currentIndex < $this->inputLen; $this->currentIndex++) {
            $this->checkCurrentOffsets();

            if ($this->isStartingString()) {
                $this->skipToEndOfString();

                continue;
            }

            if ($this->cur == $structureStart) {
                $structureCount++;
            } elseif ($this->cur == $structureEnd) {
                $structureCount--;
            }

            $this->currentContent[] = $this->cur;

            if ($structureCount <= 0) {
                break;
            }
        }
    }

    private function isBreakableWhitespace(?string $char): bool
    {
        if ($char == ',') {
            return true;
        }

        return false;
    }

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
                        $results[] = trim(implode('', $this->currentContent));
                        $this->currentContent = [];

                        continue;
                    }
                }

                continue;
            }

            if ($this->cur == '[') {
                $this->skipToEndOfPairedStructure('[', ']');

                if ($this->isBreakableWhitespace($this->next) || $this->next == null) {
                    if (count($this->currentContent) > 0) {
                        $results[] = trim(implode('', $this->currentContent));
                        $this->currentContent = [];

                        continue;
                    }
                }

                continue;
            } elseif ($this->cur == '(') {
                $this->skipToEndOfPairedStructure('(', ')');

                if ($this->isBreakableWhitespace($this->next) || $this->next == null) {
                    if (count($this->currentContent) > 0) {
                        $results[] = trim(implode('', $this->currentContent));
                        $this->currentContent = [];

                        continue;
                    }
                }

                continue;
            }

            if ($this->isBreakableWhitespace($this->next) || $this->next == null) {
                if (count($this->currentContent) > 0) {
                    $this->currentContent[] = $this->cur;
                    $results[] = trim(implode('', $this->currentContent));
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
