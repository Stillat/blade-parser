<?php

namespace Stillat\BladeParser\Document\Structures\Concerns;

use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\NodeCollection;
use Stillat\BladeParser\Nodes\Structures\CaseStatement;
use Stillat\BladeParser\Nodes\Structures\SwitchStatement;
use Stillat\BladeParser\Parser\BladeKeywords;

trait ConstructsSwitchStatements
{
    protected function constructSwitchStatements(array $nodes): void
    {
        for ($i = 0; $i < count($nodes); $i++) {
            $node = $nodes[$i];

            if (! ($node instanceof DirectiveNode && $node->isClosingDirective == false && $this->getSimplifiedStructureName($node) == BladeKeywords::K_Switch)) {
                continue;
            }

            $switchStatement = new SwitchStatement();
            $switchStatement->constructedFrom = $node;
            $node->isStructure = true;
            $node->structure = $switchStatement;

            $switchNodes = $node->getDirectChildren();
            $partitioned = $switchNodes->partitionOnDirectives([BladeKeywords::K_Case, BladeKeywords::K_Default]);
            $switchStatement->leadingNodes = $partitioned->leadingNodes->all();

            foreach ($partitioned->partitions as $partition) {
                $switchStatement->cases[] = $this->createCaseStatement($partition, $switchStatement);
            }
        }
    }

    /**
     * Constructs a case statement from the provided nodes.
     *
     * @param  NodeCollection  $nodes  The case nodes.
     * @param  SwitchStatement  $switch  The parent switch statement.
     */
    private function createCaseStatement(NodeCollection $nodes, SwitchStatement $switch): CaseStatement
    {
        $case = new CaseStatement($switch);
        $case->constructedFrom = $nodes->first();

        if ($case->constructedFrom != null) {
            $case->constructedFrom->isStructure = true;
            $case->constructedFrom->structure = $case;
        }

        $nodes->shift();

        /** @var AbstractNode $node */
        foreach ($nodes as $node) {
            $node->parent = $case->constructedFrom;
        }

        $case->caseBody = $nodes->all();
        $case->constructedFrom->childNodes = $nodes->all();

        return $case;
    }
}
