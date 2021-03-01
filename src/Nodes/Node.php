<?php

namespace Stillat\BladeParser\Nodes;

use Stillat\BladeParser\Parsers\Directives\LanguageDirective;

abstract class Node
{
    public $rawContent = '';
    public $innerContent = '';

    public $parentTypeIndex = -1;
    public $parent = null;

    public $strictEnclosedCount = 0;

    public $childTypeCount = [];

    /**
     * The child nodes, if any.
     *
     * @var Node[]
     */
    public $nodes = [];

    /**
     * The associated language directive.
     *
     * @var LanguageDirective|null
     */
    public $directive = null;

    /**
     * The nodes start index in the input stream.
     *
     * @var int
     */
    public $start = -1;

    /**
     * The nodes end index in the input stream.
     *
     * @var int
     */
    public $end = -1;

    public function innerContent()
    {
        if ($this->innerContent === null) {
            return '';
        }

        return $this->innerContent;
    }

    public function innerContentIsIntegerEquivalent()
    {
        if (strval(trim($this->innerContent())) == strval(intval($this->innerContent()))) {
            return true;
        }

        return false;
    }

    public function hasInnerExpression()
    {
        if ($this->innerContent === null) {
            return false;
        }

        return mb_strlen($this->innerContent) > 0;
    }

    public function isFirstOfType()
    {
        return $this->parentTypeIndex === 1;
    }

    public function getSubType()
    {
        if ($this instanceof CommentNode) {
            return 'comment';
        }

        $subType = 'literal';

        if ($this->directive !== null) {
            $subType = trim($this->directive);
        }

        return $subType;
    }

    public function getType()
    {
        $subType = $this->getSubType();

        $classParts = explode('\\', __CLASS__);
        $className = array_pop($classParts);

        if ($className === 'Node') {
            return 'node:'.$subType;
        }

        return mb_strtolower(mb_substr($className, 0, -4).':'.$subType);
    }
}
