<?php

namespace Stillat\BladeParser\Analyzers;

class ArraySearch
{
    /**
     * Removes consecutive integers from the haystack and returns a new array.
     *
     *  [5,6,7,10] => [10]
     *  [1,2,5,7,8] => [5]
     *
     * @param int[] $haystack The haystack.
     * @return array
     */
    public static function removeConsecutiveIntegers($haystack)
    {
        $haystackLength = count($haystack);

        if ($haystackLength <= 1) {
            return $haystack;
        }

        $newValues = [];

        $lastValue = null;

        for ($i = 0; $i < $haystackLength; $i++) {
            if ($i === 0) {
                $lastValue = $haystack[$i];
                continue;
            }

            $diff = $haystack[$i] - $lastValue;

            if ($diff === 1 || $diff === 0) {
                $lastValue = $haystack[$i];
                continue;
            } else {
                $seekIndex = $i - 2;

                if ($seekIndex < 0) {
                    $diff = $haystack[$i] - $lastValue;

                    if ($diff >= 2) {
                        $newValues[] = $lastValue;
                    }
                } else {
                    $sanityCheck = $haystack[$seekIndex];
                    $checkVal = $lastValue - $sanityCheck;

                    if ($checkVal > 1) {
                        $newValues[] = $lastValue;
                    }
                }

                $lastValue = $haystack[$i];
            }
        }

        $lastHaystackValue = $haystack[$haystackLength - 1];
        $lastDiff = $lastHaystackValue - $haystack[$haystackLength - 2];

        if ($lastDiff >= 2) {
            $newValues[] = $lastValue;
        }

        return $newValues;
    }

    public static function searchStrings($needle, $haystack)
    {
        return self::search(mb_str_split($needle), mb_str_split($haystack), true);
    }

    /**
     * Searches for all instances of the needle in the haystack array.
     *
     * @param array $needle The pattern to search for.
     * @param array $haystack The data to search in.
     * @param false $preventDoubleFirstChar Whether to allow patterns that have the first character repeated.
     * @return array
     */
    public static function search($needle, $haystack, $preventDoubleFirstChar = false)
    {
        if (count($needle) === 0) {
            return [];
        }

        $haystackLength = count($haystack);
        $needleLength = count($needle);

        $possibleKeys = array_keys($haystack, $needle[0], true);

        if ($preventDoubleFirstChar === true) {
            $possibleKeys = self::removeConsecutiveIntegers($possibleKeys);
        }

        $results = [];

        foreach ($possibleKeys as $index) {
            // start searching
            $i = $index;
            $j = 0;

            while ($i < $haystackLength && $j < $needleLength) {
                if ($haystack[$i] !== $needle[$j]) {
                    continue 2; // no match
                }
                $i++;
                $j++;
            }

            $results[] = range($index, $index + $needleLength - 1);
        }

        return $results;
    }

    /**
     * Creates a lookup table from the provided data.
     *
     * [[32,33], [53,54], [67,68]] becomes:
     *     [
     *          32 => [32,33],
     *          53 => [53,54],
     *          67 => [67,68]
     *     ]
     *
     * @param array $results The data to index.
     * @return array
     */
    public static function createIndexTable($results)
    {
        $index = [];

        foreach ($results as $result) {
            if (is_array($result) && count($results) > 0) {
                $index[$result[0]] = $result;
            }
        }

        return $index;
    }
}
