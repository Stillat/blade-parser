<?php

namespace Stillat\BladeParser\Nodes\Structures;

use Illuminate\Support\Collection;
use Stillat\BladeParser\Parser\BladeKeywords;

class Condition extends BaseStructureNode
{
    /**
     * The conditional branches.
     *
     * @var ConditionalBranch[]
     */
    public array $branches = [];

    public function getBranches(): Collection
    {
        return collect($this->branches);
    }

    public function getPrimaryBranch(): ?ConditionalBranch
    {
        if (count($this->branches) == 0) {
            return null;
        }

        return $this->branches[0];
    }

    public function getElseBranches(): Collection
    {
        return $this->getBranches()->filter(function (ConditionalBranch $branch) {
            return $branch->target->content == BladeKeywords::K_ELSE;
        })->values();
    }

    public function hasElseBranch(): bool
    {
        return $this->getElseBranches()->count() > 0;
    }

    public function getElseIfBranches(): Collection
    {
        return $this->getBranches()->filter(function (ConditionalBranch $branch) {
            return $branch->target->content == BladeKeywords::K_ElseIf;
        })->values();
    }

    public function hasElseIfBranches(): bool
    {
        return $this->getElseIfBranches()->count() > 0;
    }

    public function getConditionText(): Collection
    {
        return $this->getBranches()->map(function (ConditionalBranch $branch) {
            return $branch->getText();
        })->filter(fn ($v) => $v != null)->values();
    }

    public function containsDuplicateConditions(): bool
    {
        $existing = [];

        foreach ($this->branches as $branch) {
            $branchText = $branch->getText();

            if (array_key_exists($branchText, $existing)) {
                return true;
            }

            $existing[$branchText] = true;
        }

        return false;
    }

    public function hasEmptyBranches(): bool
    {
        foreach ($this->branches as $branch) {
            if ($branch->isEmpty()) {
                return true;
            }
        }

        return false;
    }

    public function isValid(): bool
    {
        if ($this->hasEmptyBranches()) {
            return false;
        }

        $primary = $this->getPrimaryBranch();

        if ($primary == null) {
            return false;
        }
        if ($primary->target->isClosingDirective) {
            return false;
        }
        if ($primary->target->content == BladeKeywords::K_If || $primary->target->content == BladeKeywords::K_Unless) {
            return true;
        }

        return false;
    }

    public function isUnless(): bool
    {
        $primary = $this->getPrimaryBranch();

        if ($primary == null) {
            return false;
        }

        return $primary->target->content == BladeKeywords::K_Unless;
    }
}
