<?php

namespace Stillat\BladeParser\Document\NodeUtilities;

use Illuminate\Support\Collection;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\Structures\Condition;
use Stillat\BladeParser\Nodes\Structures\ForElse;
use Stillat\BladeParser\Nodes\Structures\SwitchStatement;

trait QueriesStructures
{
    /**
     * Returns all the document structures.
     *
     * This method automatically performs structural analysis on the document.
     */
    public function getAllStructures(): Collection
    {
        $this->resolveStructures();

        $structures = [];

        foreach ($this->getNodes() as $node) {
            if ($node instanceof DirectiveNode && $node->structure != null) {
                $structures[] = $node->structure;
            }
        }

        return collect($structures);
    }

    /**
     * Returns the direct document structures.
     *
     * This method automatically performs structural analysis. Only
     * structures that are at the root of the document, without any
     * parent node, will be returned.
     */
    public function getRootStructures(): Collection
    {
        $this->resolveStructures();

        $structures = [];

        foreach ($this->getRootNodes() as $node) {
            if ($node instanceof DirectiveNode && $node->structure != null) {
                $structures[] = $node->structure;
            }
        }

        return collect($structures);
    }

    /**
     * Returns all the document's switch statements.
     *
     * This method automatically performs structural analysis.
     */
    public function getAllSwitchStatements(): Collection
    {
        $switchStatements = [];

        foreach ($this->getAllStructures() as $structure) {
            if ($structure instanceof SwitchStatement) {
                $switchStatements[] = $structure;
            }
        }

        return collect($switchStatements);
    }

    /**
     * Returns all the direct switch statements.
     *
     * This method automatically performs structural analysis. Only
     * `@switch` statements that appear at the root of the document,
     * without any parent nodes, will be returned.
     */
    public function getRootSwitchStatements(): Collection
    {
        $switchStatements = [];

        foreach ($this->getRootStructures() as $structure) {
            if ($structure instanceof SwitchStatement) {
                $switchStatements[] = $structure;
            }
        }

        return collect($switchStatements);
    }

    /**
     * Returns all the document's conditions.
     *
     * This method automatically performs structural analysis.
     */
    public function getAllConditions(): Collection
    {
        $conditions = [];

        foreach ($this->getAllStructures() as $structure) {
            if ($structure instanceof Condition) {
                $conditions[] = $structure;
            }
        }

        return collect($conditions);
    }

    /**
     * Returns all the document's root conditions.
     *
     * This method automatically performs structural analysis. Only structures
     * that appear at the root of the document, without any parent node, will
     * be returned.
     */
    public function getRootConditions(): Collection
    {
        $conditions = [];

        foreach ($this->getRootStructures() as $structure) {
            if ($structure instanceof Condition) {
                $conditions[] = $structure;
            }
        }

        return collect($conditions);
    }

    /**
     * Returns all the document's for-else blocks.
     *
     * This method automatically performs structural analysis.
     */
    public function getAllForElse(): Collection
    {
        $forElseBlocks = [];

        foreach ($this->getAllStructures() as $structure) {
            if ($structure instanceof ForElse) {
                $forElseBlocks[] = $structure;
            }
        }

        return collect($forElseBlocks);
    }

    /**
     * Returns the direct for-else blocks.
     *
     * This method automatically performs structural analysis. Only
     * nodes that appear at the root of the document, without any
     * parent nodes, will be returned.
     */
    public function getRootForElse(): Collection
    {
        $forElseBlocks = [];

        foreach ($this->getRootStructures() as $structure) {
            if ($structure instanceof ForElse) {
                $forElseBlocks[] = $structure;
            }
        }

        return collect($forElseBlocks);
    }
}
