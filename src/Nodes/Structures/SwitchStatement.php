<?php

namespace Stillat\BladeParser\Nodes\Structures;

use Illuminate\Support\Collection;
use Stillat\BladeParser\Document\NodeUtilities\QueriesGeneralNodes;
use Stillat\BladeParser\Nodes\NodeCollection;
use Stillat\BladeParser\Parser\BladeKeywords;

class SwitchStatement extends BaseStructureNode
{
    use QueriesGeneralNodes;

    public array $leadingNodes = [];

    /**
     * @var CaseStatement[]
     */
    public array $cases = [];

    public function getRootNodes(): NodeCollection
    {
        return $this->constructedFrom->getRootNodes();
    }

    public function getDirectChildren(): NodeCollection
    {
        return $this->constructedFrom->getDirectChildren();
    }

    public function getCases(): Collection
    {
        return collect($this->cases);
    }

    public function getConditionCases(): Collection
    {
        return $this->getCases()->filter(function (CaseStatement $case) {
            return $case->isDefaultCase() == false;
        })->values();
    }

    public function getDefaultCases(): Collection
    {
        return $this->getCases()->filter(function (CaseStatement $case) {
            return $case->isDefaultCase();
        })->values();
    }

    public function isValid(): bool
    {
        return ! $this->hasInvalidCases();
    }

    public function hasInvalidCases(): bool
    {
        foreach ($this->cases as $case) {
            if (! $case->isValid()) {
                return true;
            }
        }

        return false;
    }

    public function getLeadingNodes(): Collection
    {
        return collect($this->leadingNodes);
    }

    public function getCaseCount(): int
    {
        return $this->getCases()->filter(function (CaseStatement $case) {
            $condition = $case->getCondition();

            if ($condition != null && $condition->content == BladeKeywords::K_Case) {
                return true;
            }

            return false;
        })->count();
    }

    public function getDefaultCount(): int
    {
        return $this->getCases()->filter(function (CaseStatement $case) {
            $condition = $case->getCondition();

            if ($condition != null && $condition->content == BladeKeywords::K_Default) {
                return true;
            }

            return false;
        })->count();
    }

    /**
     * Tests if the switch statement contains a default case.
     */
    public function hasDefaultCase(): bool
    {
        foreach ($this->cases as $case) {
            if ($case->isDefaultCase()) {
                return true;
            }
        }

        return false;
    }
}
