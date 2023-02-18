<?php

namespace Stillat\BladeParser\Nodes\Concerns;

use Stillat\BladeParser\Nodes\Structures\CaseStatement;
use Stillat\BladeParser\Nodes\Structures\Condition;
use Stillat\BladeParser\Nodes\Structures\ForElse;
use Stillat\BladeParser\Nodes\Structures\SwitchStatement;

trait ProvidesAccessToResolvedStructures
{
    public function getForElse(): ?ForElse
    {
        if ($this->structure instanceof ForElse) {
            return $this->structure;
        }

        return null;
    }

    public function hasForElse(): bool
    {
        return $this->getForElse() != null;
    }

    public function getCondition(): ?Condition
    {
        if ($this->structure instanceof Condition) {
            return $this->structure;
        }

        return null;
    }

    public function hasCondition(): bool
    {
        return $this->getCondition() != null;
    }

    public function getCaseStatement(): ?CaseStatement
    {
        if ($this->structure instanceof CaseStatement) {
            return $this->structure;
        }

        return null;
    }

    public function hasCaseStatement(): bool
    {
        return $this->getCaseStatement() != null;
    }

    public function getSwitchStatement(): ?SwitchStatement
    {
        if ($this->structure instanceof SwitchStatement) {
            return $this->structure;
        }

        return null;
    }

    public function hasSwitchStatement(): bool
    {
        return $this->getSwitchStatement() != null;
    }
}
