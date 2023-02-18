<?php

namespace Stillat\BladeParser\Document\Structures\Concerns;

use Stillat\BladeParser\Nodes\DirectiveNode;

trait ManagesDirectiveIndexes
{
    private function buildCloseIndex(array $nodes): void
    {
        foreach ($nodes as $directive) {
            if (! $directive instanceof DirectiveNode) {
                continue;
            }

            if (! $directive->isClosingDirective) {
                continue;
            }

            if (! array_key_exists($this->stackCount, $this->closingDirectiveIndex)) {
                $this->closingDirectiveIndex[$this->stackCount] = [];
            }

            if (! array_key_exists($this->stackCount, $this->closingDirectiveIndexCount)) {
                $this->closingDirectiveIndexCount[$this->stackCount] = [];
            }

            $directiveName = $this->getSimplifiedStructureName($directive);

            if (! array_key_exists($directiveName, $this->closingDirectiveIndex[$this->stackCount])) {
                $this->closingDirectiveIndex[$this->stackCount][$directiveName] = [];
                $this->closingDirectiveIndexCount[$this->stackCount][$directiveName] = 0;
            }

            $this->closingDirectiveIndex[$this->stackCount][$directiveName][] = $directive;
            $this->closingDirectiveIndexCount[$this->stackCount][$directiveName] += 1;
        }

        if (array_key_exists($this->stackCount, $this->closingDirectiveNames)) {
            $this->closingDirectiveNames[$this->stackCount] = [];
        }

        // Process the closing tag index, if it has been set for the current stack level.
        if (array_key_exists($this->stackCount, $this->closingDirectiveIndex)) {
            $directiveNames = array_keys($this->closingDirectiveIndex[$this->stackCount]);
            $this->closingDirectiveNames[$this->stackCount] = $directiveNames;

            foreach ($this->closingDirectiveIndex[$this->stackCount] as $directiveName => $indexedNodes) {
                $indexedNodeCount = count($indexedNodes);

                if ($indexedNodeCount == 0) {
                    continue;
                }

                // Find the last closing directive candidate and work
                // up to calculate a list of valid opening candidates.
                /** @var DirectiveNode $lastIndexedNode */
                $lastIndexedNode = $indexedNodes[$indexedNodeCount - 1];

                for ($i = 0; $i < $indexedNodeCount; $i++) {
                    $node = $nodes[$i];

                    if ($node instanceof DirectiveNode) {
                        if ($node->index >= $lastIndexedNode->index) {
                            break;
                        }

                        $nodeDirectiveName = $this->getSimplifiedStructureName($node);

                        if (! $node->isClosingDirective && $nodeDirectiveName == $directiveName) {
                            if (! array_key_exists($this->stackCount, $this->openDirectiveIndexCount)) {
                                $this->openDirectiveIndexCount[$this->stackCount] = [];
                            }

                            if (! array_key_exists($directiveName, $this->openDirectiveIndexCount[$this->stackCount])) {
                                $this->openDirectiveIndexCount[$this->stackCount][$directiveName] = 0;
                            }

                            $this->openDirectiveIndexCount[$this->stackCount][$directiveName] += 1;
                        }
                    }
                }
            }
        }
    }

    private function canClose(string $directiveName): bool
    {
        $checkName = 'end'.mb_strtolower($directiveName);

        return array_key_exists($checkName, $this->possibleClosingDirectives);
    }

    /**
     * Returns a list of closing structure name candidates.
     *
     * @return string[]
     */
    private function getClosingCandidates(DirectiveNode $node): array
    {
        $candidates = [];

        $candidates[] = $this->getSimplifiedStructureName($node);

        return $candidates;
    }

    /**
     * @return string[]
     */
    private function getScanForList(DirectiveNode $node): array
    {
        if (! array_key_exists($this->stackCount, $this->closingDirectiveNames)) {
            return [];
        }

        if (! $node->isClosingDirective) {
            $candidates = $this->getClosingCandidates($node);
            $indexValues = $this->closingDirectiveNames[$this->stackCount];

            return array_intersect($candidates, $indexValues);
        }

        return [];
    }
}
