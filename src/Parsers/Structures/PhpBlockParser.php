<?php

namespace Stillat\BladeParser\Parsers\Structures;

use Stillat\BladeParser\Analyzers\ArraySearch;

class PhpBlockParser
{
    const START_PHP = '@php';
    const NAME_PHP = 'php';
    const END_PHP = '@endphp';
    const NAME_END_PHP = 'endphp';
    const PHP_DIRECTIVE_INPUT_START = '(';

    protected $phpStart = [];
    protected $phpStartCount = -1;
    protected $phpEnd = [];
    protected $phpEndCount = -1;
    protected $candidateStartLocations = [];
    protected $literalPhpLocations = [];
    protected $endLocations = [];
    protected $endLocationCount = -1;

    protected $cachedOffsets = null;
    protected $invalidLiteralLocations = [];
    protected $extractions = [];
    protected $tokens = [];
    protected $tokenLength = -1;
    protected $phpPairs = [];

    public function __construct()
    {
        $this->phpStart = mb_str_split(self::START_PHP);
        $this->phpEnd = mb_str_split(self::END_PHP);
        $this->phpStartCount = count($this->phpStart);
        $this->phpEndCount = count($this->phpEnd);
    }

    /**
     * Resets the internal state between parsing runs.
     */
    public function reset()
    {
        $this->cachedOffsets = null;
        $this->phpPairs = [];
        $this->invalidLiteralLocations = [];
        $this->candidateStartLocations = [];
        $this->literalPhpLocations = [];
        $this->endLocations = [];
        $this->endLocationCount = -1;
    }

    /**
     * Returns the extraction at the provided index.
     *
     * @param int $index The index.
     * @return array
     */
    public function getExtraction($index)
    {
        if (array_key_exists($index, $this->extractions)) {
            return $this->extractions[$index];
        }

        return null;
    }

    public function isValidPhpBlockStartLocation($index)
    {
        return array_key_exists($index, $this->extractions);
    }

    /**
     * Sets the list of tokens to parse.
     *
     * @param string[] $tokens The tokens.
     */
    public function setTokens($tokens)
    {
        $this->tokens = $tokens;
        $this->tokenLength = count($tokens);
    }

    public function getPairOffsets()
    {
        if ($this->cachedOffsets === null) {
            $offsets = [];
            foreach ($this->extractions as $extraction) {
                $offsets[] = [
                    'start' => $extraction['start'],
                    'content_start' => $extraction['start'] + $this->phpStartCount,
                    'end' => $extraction['end'],
                    'content_end' => $extraction['end'] - $this->phpEndCount,
                ];
            }

            $this->cachedOffsets = $offsets;
        }

        return $this->cachedOffsets;
    }

    public function getPairs()
    {
        return $this->phpPairs;
    }

    public function parse()
    {
        $this->reset();
        $this->candidateStartLocations = ArraySearch::search($this->phpStart, $this->tokens);
        $this->endLocations = ArraySearch::search($this->phpEnd, $this->tokens);
        $this->endLocationCount = count($this->endLocations);

        $this->createPairs()
            ->createLiteralIndex()
            ->createExtractions();
    }

    private function createExtractions()
    {
        $extractions = [];

        foreach ($this->phpPairs as $pair) {
            if (count($pair) != 2) {
                continue;
            }

            $pairStart = $pair[0][0];
            $pairEnd = $pair[1][count($pair[1]) - 1];
            $pairLength = $pairEnd - $pairStart + 1;

            $extractedParts = array_slice($this->tokens, $pairStart, $pairLength);
            $adjustedContent = array_slice($extractedParts, $this->phpStartCount, count($extractedParts) - $this->phpEndCount - $this->phpStartCount);

            $rawPieces = explode("\n", implode($extractedParts));
            $trimStart = false;
            $previousIndex = $pairStart - 1;

            if (trim($rawPieces[0]) == self::START_PHP && ($previousIndex >= 0 && $this->tokens[$previousIndex] == "\n")) {
                $trimStart = true;
            }

            $extractions[$pairStart] = [
                'start' => $pairStart,
                'end' => $pairEnd,
                'raw_pair' => $pair,
                'raw_content' => implode($extractedParts),
                'content' => implode($adjustedContent),
            ];
        }

        $this->extractions = $extractions;
    }

    private function createPairs()
    {
        if (count($this->endLocations) === 0) {
            return $this;
        }

        $pairs = [];

        $lastGatheredEndIndex = -1;
        $seekStart = 0;
        foreach ($this->candidateStartLocations as $location) {
            $startLocation = $location[0];

            if ($startLocation < $lastGatheredEndIndex) {
                $this->invalidLiteralLocations[$startLocation] = true;
                continue;
            }

            $candidate = null;

            foreach ($this->endLocations as $endLocation) {
                $endStartLocation = $endLocation[0];

                if ($endStartLocation > $startLocation) {
                    $candidate = $endLocation;
                    $lastGatheredEndIndex = $endStartLocation;
                    break;
                }
            }

            if ($candidate != null) {
                $this->invalidLiteralLocations[$startLocation] = true;
                $this->invalidLiteralLocations[$lastGatheredEndIndex] = true;
                $pairs[] = [
                    $location, $candidate,
                ];
            }
        }

        $this->phpPairs = $pairs;

        return $this;
    }

    private function createLiteralIndex()
    {
        foreach ($this->candidateStartLocations as $location) {
            $startLocation = $location[0];

            if ($this->isInvalidLiteralLocation($startLocation)) {
                continue;
            }

            $lastIndex = $location[count($location) - 1];
            $seekPosition = $lastIndex + 1;

            if ($seekPosition >= $this->tokenLength) {
                $this->literalPhpLocations[$startLocation] = true;
            } else {
                if (ctype_space($this->tokens[$seekPosition]) == true) {
                    $this->literalPhpLocations[$startLocation] = true;
                }
            }
        }

        return $this;
    }

    /**
     * Tests if the provided index is an invalid location for a literal @php directive.
     *
     * @param int $index The index.
     * @return bool
     */
    public function isInvalidLiteralLocation($index)
    {
        return array_key_exists($index, $this->invalidLiteralLocations);
    }

    /**
     * Tests if the provided index marks the start of a literal @php directive.
     *
     * @param int $index The index.
     * @return bool
     */
    public function isLiteralPhp($index)
    {
        return array_key_exists($index, $this->literalPhpLocations);
    }
}
