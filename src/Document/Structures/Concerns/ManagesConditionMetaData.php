<?php

namespace Stillat\BladeParser\Document\Structures\Concerns;

use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Parser\BladeKeywords;

trait ManagesConditionMetaData
{
    protected array $conditionClosingPairs = ['elseif', 'else', 'endif'];

    protected array $nodeReferences = [];

    protected function getReferenceCount(DirectiveNode $node): int
    {
        if (! array_key_exists($node->id, $this->nodeReferences)) {
            $this->nodeReferences[$node->id] = 0;
        }

        return $this->nodeReferences[$node->id];
    }

    protected function incrementReferenceCount(DirectiveNode $node): void
    {
        $currentCount = $this->getReferenceCount($node);
        $this->nodeReferences[$node->id] = $currentCount + 1;
    }

    /**
     * Tests if the provided node is a condition-like node.
     *
     * @param  DirectiveNode  $node  The node.
     */
    public function isConditionalStructure(DirectiveNode $node): bool
    {
        if ($node->content == BladeKeywords::K_ELSE) {
            return true;
        }

        $name = $this->getSimplifiedStructureName($node);

        if ($name == BladeKeywords::K_If || $name == BladeKeywords::K_ElseIf || $name == BladeKeywords::K_ELSE || $name == BladeKeywords::K_EndIf) {
            return true;
        }

        return array_key_exists($name, $this->speculativeConditions);
    }

    private function getConditionValidClosingPairs(string $name): array
    {
        if ($name == BladeKeywords::K_ELSE) {
            return [BladeKeywords::K_If];
        }

        if ($name == BladeKeywords::K_If || $name == BladeKeywords::K_ElseIf) {
            return $this->conditionClosingPairs;
        }

        return [];
    }

    /**
     * Tests if the provided condition-like node requires a closing directive.
     *
     * @param  DirectiveNode  $node  The node.
     */
    public function conditionRequiresClose(DirectiveNode $node): bool
    {
        $name = $this->getSimplifiedStructureName($node);

        if ($name == BladeKeywords::K_ElseIf || $name == BladeKeywords::K_ELSE) {
            if ($node->isClosedBy != null) {
                return false;
            }

            return true;
        }

        if ($node->isClosingDirective) {
            return false;
        }

        if ($node->isClosedBy != null) {
            return false;
        }

        return true;
    }
}
