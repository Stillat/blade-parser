<?php

namespace Stillat\BladeParser\Parsers;

use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Parsers\Directives\LanguageDirective;

class DirectiveStack
{

    protected $stack = [];

    public function push(DirectiveNode $directive)
    {
        if (array_key_exists($directive->directive, $this->stack) == false) {
            $this->stack[$directive->directive] = [];
        }

        array_unshift($this->stack[$directive->directive], $directive);
    }

    public function findParent(LanguageDirective $languageDirective)
    {
        if ($languageDirective->mustAppearIn == null || count($languageDirective->mustAppearIn) === 0) {
            return null;
        }

        foreach ($this->stack as $type => $directiveStack) {
            if (in_array($type, $languageDirective->mustAppearIn)) {
                if (count($directiveStack) > 0) {
                    /** @var DirectiveNode $first */
                    $first = $directiveStack[0];

                    if (array_key_exists($languageDirective->name, $first->childTypeCount) === false) {
                        $first->childTypeCount[$languageDirective->name] = 0;
                    }

                    $first->childTypeCount[$languageDirective->name] += 1;
                    $currentChildCount = $first->childTypeCount[$languageDirective->name];

                    return [$first, $currentChildCount];
                }
            }
        }

        return null;
    }


}