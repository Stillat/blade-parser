<?php

namespace Stillat\BladeParser\Compiler\CompilerServices;

use Stillat\BladeParser\Nodes\Loops\LoopVariables;
use Stillat\BladeParser\Parser\BladeKeywords;

class LoopVariablesExtractor
{
    protected StringSplitter $splitter;

    public function __construct()
    {
        $this->splitter = new StringSplitter;
    }

    /**
     * Extracts information about the loop variables in the provided value.
     *
     * @param  string  $value  The content
     */
    public function extractDetails(string $value): LoopVariables
    {
        $result = new LoopVariables;
        $result->source = $value;

        $value = StringUtilities::unwrapParentheses($value);
        $split = $this->splitter->split($value);

        $asKeywordLocation = null;

        for ($i = 0; $i < count($split); $i++) {
            if (mb_strtolower($split[$i]) == BladeKeywords::K_As) {
                $asKeywordLocation = $i;
                break;
            }
        }

        if ($asKeywordLocation == null) {
            $result->isValid = false;
        } else {
            $alias = implode(' ', array_slice($split, $i + 1));
            $variable = implode(' ', array_slice($split, 0, $i));

            if (mb_strlen($alias) > 0 && mb_strlen($variable) > 0) {
                $result->isValid = true;
                $result->alias = $alias;
                $result->variable = $variable;
            }
        }

        return $result;
    }
}
