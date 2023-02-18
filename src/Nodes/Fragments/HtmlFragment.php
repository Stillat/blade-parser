<?php

namespace Stillat\BladeParser\Nodes\Fragments;

class HtmlFragment extends Fragment
{
    /**
     * Indicates if the HTML fragment is a closing tag.
     */
    public bool $isClosingTag = false;

    /**
     * Indicates if the HTML fragment is a self-closing tag.
     */
    public bool $isSelfClosing = false;

    /**
     * The fragment's document content.
     */
    public string $documentContent = '';

    /**
     * The fragment's inner content.
     */
    public ?Fragment $innerContent = null;

    /**
     * The fragment's name.
     */
    public ?Fragment $name = null;

    /**
     * The fragment's tag name, if available.
     */
    public string $tagName = '';

    /**
     * @var FragmentParameter[]
     */
    public array $parameters = [];

    /**
     * Tests if the fragment contains the provided parameter.
     *
     * @param  string  $name The parameter name.
     */
    public function hasParameter(string $name): bool
    {
        return $this->getParameter($name) != null;
    }

    /**
     * Retrieves the parameter with the given name, if it exists.
     *
     * @param  string  $name The parameter name.
     */
    public function getParameter(string $name): ?FragmentParameter
    {
        foreach ($this->parameters as $attribute) {
            if ($attribute->name == $name) {
                return $attribute;
            }
        }

        return null;
    }
}
