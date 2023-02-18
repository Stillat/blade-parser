<?php

namespace Stillat\BladeParser\Parser\HtmlFragments;

use Stillat\BladeParser\Nodes\Fragments\FragmentParameterType;
use Stillat\BladeParser\Nodes\Fragments\FragmentPosition;
use Stillat\BladeParser\Nodes\Fragments\HtmlFragment;

class FragmentPositionsAnalyzer
{
    /**
     * The fragments to be analyzed.
     *
     * @var HtmlFragment[]
     */
    protected array $fragments = [];

    /**
     * Sets the HTML fragments to be analyzed.
     *
     * @param  HtmlFragment[]  $fragments
     */
    public function setFragments(array $fragments): void
    {
        $this->fragments = $fragments;
    }

    /**
     * Retrieves the contextual location for the provided position.
     */
    public function getContext(int $position): FragmentPosition
    {
        $loc = FragmentPosition::Unknown;

        foreach ($this->fragments as $fragment) {
            if ($fragment->position->contains($position)) {
                $loc = FragmentPosition::InsideFragment;

                // Check if the position is within the
                // characters making up the tag start.
                if ($position == $fragment->position->startOffset || ($fragment->isClosingTag && $position == $fragment->position->startOffset + 1)) {
                    $loc = FragmentPosition::StartOfFragment;
                    break;
                }

                // Check if the position is within the
                // characters making up the tag end.
                if ($position == $fragment->position->endOffset || ($fragment->isSelfClosing && $position == $fragment->position->endOffset - 1)) {
                    $loc = FragmentPosition::EndOfFragment;
                    break;
                }

                // Check if the position is within the tag name.
                if ($fragment->name != null &&
                    $fragment->name->position->contains($position)) {
                    $loc = FragmentPosition::InsideFragmentName;
                    break;
                }

                // Check if the position is inside an attribute.
                foreach ($fragment->parameters as $attr) {
                    if ($attr->position->contains($position)) {
                        $loc = FragmentPosition::InsideAttribute;

                        if ($attr->type == FragmentParameterType::Parameter) {
                            $loc = FragmentPosition::InsideParameter;
                        }

                        break;
                    }
                }

                break;
            }
        }

        return $loc;
    }
}
