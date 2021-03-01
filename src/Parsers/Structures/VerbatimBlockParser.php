<?php

namespace Stillat\BladeParser\Parsers\Structures;

use Stillat\BladeParser\Analyzers\ArraySearch;

class VerbatimBlockParser
{
    const START_VERBATIM = '@verbatim';
    const VERBATIM = 'verbatim';
    const END_VERBATIM = '@endverbatim';
    const ENDVERBATIM = 'endverbatim';

    protected $verbatimStart = [];
    protected $verbatimEnd = [];
    protected $verbatimStartCount = -1;
    protected $verbatimEndCount = -1;
    protected $tokens = [];
    protected $startLocations = [];
    protected $endLocations = [];
    protected $startIndex = [];
    protected $endIndex = [];
    protected $isBalanced = false;
    protected $extractions = [];
    protected $pairs = [];
    protected $cachedOffsets = null;
    protected $endTagIndex = [];

    public function __construct()
    {
        $this->verbatimStart = mb_str_split(self::START_VERBATIM);
        $this->verbatimEnd = mb_str_split(self::END_VERBATIM);
        $this->verbatimStartCount = count($this->verbatimStart);
        $this->verbatimEndCount = count($this->verbatimEnd);
    }

    /**
     * Sets the list of tokens to parse.
     *
     * @param string[] $tokens The tokens.
     */
    public function setTokens($tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * Resets the internal state between parsing runs.
     */
    public function reset()
    {
        $this->extractions = [];
        $this->endTagIndex = [];
        $this->pairs = [];
        $this->cachedOffsets = null;
        $this->startLocations = [];
        $this->endLocations = [];
        $this->startIndex = [];
        $this->endIndex = [];
        $this->isBalanced = false;
    }

    public function parse()
    {
        $this->reset();
        $this->startLocations = ArraySearch::search($this->verbatimStart, $this->tokens, true);
        $this->endLocations = ArraySearch::search($this->verbatimEnd, $this->tokens, false);
        $this->startIndex = ArraySearch::createIndexTable($this->startLocations);
        $this->endIndex = ArraySearch::createIndexTable($this->endLocations);

        if (count(array_keys($this->startIndex)) == count(array_keys($this->endIndex))) {
            $this->isBalanced = true;
        } else {
            $this->isBalanced = false;
        }

        $this->pairs = $this->getPairs();
        $this->extractions = $this->createExtractions($this->pairs);

        foreach ($this->pairs as $pair) {
            $this->endTagIndex[$pair[1][0]] = 1;
        }
    }

    /**
     * Tests if the provided index is the beginning of a verbatim extraction.
     *
     * @param int $index The index.
     * @return bool
     */
    public function isStartOfTagPair($index)
    {
        return array_key_exists($index, $this->extractions);
    }

    /**
     * Tests if the provided index is a valid @verbatim end position.
     * @param int $index The index.
     * @return bool
     */
    public function isValidEndPair($index)
    {
        return array_key_exists($index, $this->endTagIndex);
    }

    /**
     * Extracts meaningful information from the provided end string.
     *
     * End strings may contain literal characters
     * that must be appended to the output.
     *
     * @param string $end The string to parse.
     * @return array|null
     */
    public function parseEndTagComponents($end)
    {
        if (mb_strlen($end) < $this->verbatimEndCount) {
            return null;
        }
        $components = [];
        $prefix = mb_substr($end, 0, $this->verbatimEndCount);

        if ($prefix != self::END_VERBATIM) {
            return null;
        }

        $literal = mb_substr($end, $this->verbatimEndCount);
        $components['directive'] = $prefix;
        $components['name'] = self::ENDVERBATIM;
        $components['literal'] = $literal;
        $components['length'] = mb_strlen($end) - 1;

        return $components;
    }

    /**
     * Returns the extraction at the provided index.
     *
     * @param int $index The index.
     * @return array
     */
    public function getExtraction($index)
    {
        return $this->extractions[$index];
    }

    /**
     * Constructs a list of start/end tag pair offsets.
     *
     * @return array
     */
    public function getPairOffsets()
    {
        if ($this->cachedOffsets === null) {
            $offsets = [];
            foreach ($this->extractions as $extraction) {
                $offsets[] = [
                    'start' => $extraction['start'],
                    'content_start' => $extraction['start'] + $this->verbatimStartCount,
                    'end' => $extraction['end'],
                    'content_end' => $extraction['end'] - $this->verbatimEndCount
                ];
            }

            $this->cachedOffsets = $offsets;
        }

        return $this->cachedOffsets;
    }

    /**
     * Creates a list of all literal extractions for the provided pairs.
     *
     * @param array $pairs The pairs.
     * @return array
     */
    private function createExtractions($pairs)
    {
        $extractions = [];

        foreach ($pairs as $pair) {
            if (count($pair) != 2) {
                continue;
            }

            $pairStart = $pair[0][0];
            $pairEnd = $pair[1][count($pair[1]) - 1];
            $pairLength = $pairEnd - $pairStart + 1;

            $extractedParts = array_slice($this->tokens, $pairStart, $pairLength);
            $adjustedContent = array_slice($extractedParts, $this->verbatimStartCount, count($extractedParts) - $this->verbatimEndCount - $this->verbatimStartCount);

            $rawPieces = explode("\n", implode($extractedParts));
            $trimStart = false;
            $previousIndex = $pairStart - 1;

            if (trim($rawPieces[0]) == self::START_VERBATIM && ($previousIndex >= 0 && $this->tokens[$previousIndex] == "\n")) {
                $trimStart = true;
            }

            // Prevents additional newlines when the @verbatim was on it's own line.
            if (count($adjustedContent) > 2 && $trimStart == true) {
                if (mb_strlen(trim($adjustedContent[0])) == 0 && mb_substr_count($adjustedContent[0], "\n")) {
                    $adjustedContent[0] = trim($adjustedContent[0]);
                }
            }

            $extractions[$pairStart] = [
                'start' => $pairStart,
                'end' => $pairEnd,
                'raw_pair' => $pair,
                'raw_content' => implode($extractedParts),
                'content' => implode($adjustedContent)
            ];
        }

        return $extractions;
    }

    /**
     * Produces a set of logical directive pairs.
     *
     * @return array
     */
    private function getPairs()
    {
        $pairs = [];
        $lastIndex = -1;

        foreach ($this->startIndex as $possibleTagStart => $tagParts) {

            if ($possibleTagStart <= $lastIndex) {
                continue;
            }

            $candidate = null;

            foreach ($this->endIndex as $tagIndex => $tag) {
                if ($tagIndex > $possibleTagStart) {
                    $candidate = $tag;
                    break;
                }
            }

            if ($candidate != null && is_array($candidate) && count($candidate) > 0) {
                $pairs[] = [
                    $tagParts,
                    $candidate
                ];

                $lastIndex = $candidate[0];
            }
        }

        return $pairs;
    }

    /**
     * Returns a value indicating if the @verbatim and @endverbatim tree is well balanced.
     *
     * @return bool
     */
    public function getIsBalanced()
    {
        return $this->isBalanced;
    }

}
