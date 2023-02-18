<?php

namespace Stillat\BladeParser\Nodes\Structures;

use Stillat\BladeParser\Document\NodeUtilities\QueriesGeneralNodes;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\NodeCollection;
use Stillat\BladeParser\Parser\BladeKeywords;

class CaseStatement extends BaseStructureNode
{
    use QueriesGeneralNodes;

    private SwitchStatement $switchOwner;

    public array $caseBody = [];

    public function __construct(SwitchStatement $switch)
    {
        parent::__construct();

        $this->switchOwner = $switch;
    }

    public function getNodes(): NodeCollection
    {
        return $this->getSwitch()->getNode()->getNodesBetweenInclusive(
            $this->constructedFrom,
            $this->getBody()->last()
        )->values();
    }

    public function getRootNodes(): NodeCollection
    {
        return new NodeCollection($this->caseBody);
    }

    public function getDirectChildren(): NodeCollection
    {
        return $this->getRootNodes();
    }

    /**
     * Returns the parent switch statement.
     */
    public function getSwitch(): SwitchStatement
    {
        return $this->switchOwner;
    }

    /**
     * Returns the case statement's body nodes.
     */
    public function getBody(): NodeCollection
    {
        return new NodeCollection($this->caseBody);
    }

    /**
     * Returns the enclosed break directives.
     */
    public function getBreakDirectives(): NodeCollection
    {
        return $this->getBody()->filter(function ($node) {
            return $node instanceof DirectiveNode && $node->content == BladeKeywords::K_Break;
        })->values();
    }

    /**
     * Returns the total number of break directives under the case.
     */
    public function getBreakCount(): int
    {
        return $this->getBreakDirectives()->count();
    }

    /**
     * Tests if the case statement has a body.
     */
    public function hasBody(): bool
    {
        return count($this->caseBody) > 0;
    }

    /**
     * Test if the case statement is a "default" case.
     */
    public function isDefaultCase(): bool
    {
        $condition = $this->getCondition();

        if ($condition == null) {
            return false;
        }

        return $condition->content == BladeKeywords::K_Default;
    }

    /**
     * Returns the directive the case statement was constructed from.
     */
    public function getCondition(): ?DirectiveNode
    {
        return $this->constructedFrom;
    }

    /**
     * Tests if the case statement is valid.
     */
    public function isValid(): bool
    {
        $condition = $this->getCondition();

        if ($condition == null) {
            return false;
        }

        if ($condition->content == BladeKeywords::K_Case || $condition->content == BladeKeywords::K_Default) {
            $breakCount = $this->getBreakCount();

            if ($breakCount > 1) {
                return false;
            }

            if ($condition->content == BladeKeywords::K_Case && $breakCount == 0) {
                return false;
            }

            return true;
        }

        return false;
    }
}
