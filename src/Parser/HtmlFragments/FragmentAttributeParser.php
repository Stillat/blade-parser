<?php

namespace Stillat\BladeParser\Parser\HtmlFragments;

use Stillat\BladeParser\Nodes\Fragments\FragmentParameter;
use Stillat\BladeParser\Nodes\Fragments\FragmentParameterType;
use Stillat\BladeParser\Nodes\Fragments\HtmlFragment;
use Stillat\BladeParser\Support\Utf8StringIterator;

class FragmentAttributeParser extends BaseFragmentParser
{
    /**
     * Parses the fragment's inner content for attributes.
     */
    public function parse(HtmlFragment $fragment): array
    {
        $this->resetState();
        $this->string = new Utf8StringIterator($fragment->innerContent->content);

        $tempAttributes = [];
        $attributes = [];

        for ($i = 0; $i < count($this->string); $i++) {
            $this->checkCurrentOffsets($i);

            if (ctype_space($this->current)) {
                $this->buffer = '';

                continue;
            }

            if ($this->isStartOfString()) {
                $i = $this->scanToEndOfString($i);
                $this->checkCurrentOffsets($i);
            } else {
                $this->buffer .= $this->current;
            }

            if ($this->next == null || ctype_space($this->next)) {
                $tempAttributes[] = [
                    $this->buffer, $i,
                ];

                $this->buffer = '';

                continue;
            }
        }

        foreach ($tempAttributes as $tempAttribute) {
            $attribute = new FragmentParameter();

            $attribute->content = $tempAttribute[0];

            // Calculate the attribute's start and end
            // positions relative to the original doc.
            $attribute->position->endOffset = $tempAttribute[1] + $fragment->innerContent->position->startOffset;
            $attribute->position->startOffset = $attribute->position->endOffset - str($attribute->content)->length() + 1;

            // Extract name/values, if present.
            $parts = str($attribute->content)->explode('=', 2);

            if ($parts->count() == 2) {
                $attribute->type = FragmentParameterType::Parameter;
                $attribute->name = $parts->first();
                $attribute->value = $parts->last();
            } else {
                $attribute->name = $attribute->content;
                $attribute->type = FragmentParameterType::Attribute;
            }

            $attributes[] = $attribute;
        }

        return $attributes;
    }
}
