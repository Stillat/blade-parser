<?php

namespace Stillat\BladeParser\Document\NodeUtilities;

use Illuminate\Support\Collection;
use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\CommentNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\EchoNode;
use Stillat\BladeParser\Nodes\LiteralNode;
use Stillat\BladeParser\Nodes\NodeCollection;
use Stillat\BladeParser\Nodes\PhpBlockNode;
use Stillat\BladeParser\Nodes\PhpTagNode;

trait QueriesGenerics
{
    /**
     * Finds all nodes of the provided type.
     *
     * @param  string  $type The type to search.
     */
    public function allOfType(string $type): NodeCollection
    {
        $discoveredNodes = [];

        foreach ($this->getNodes() as $node) {
            if ($node instanceof $type) {
                $discoveredNodes[] = $node;
            }
        }

        return new NodeCollection($discoveredNodes);
    }

    /**
     * Finds all nodes that are not of the provided type.
     *
     * @param  string  $type The type to search.
     */
    public function allNotOfType(string $type): NodeCollection
    {
        $discoveredNodes = [];

        foreach ($this->getNodes() as $node) {
            if (! $node instanceof $type) {
                $discoveredNodes[] = $node;
            }
        }

        return new NodeCollection($discoveredNodes);
    }

    /**
     * Locates the first instance of the provided node type in the document.
     *
     * @param  string  $type The node type.
     */
    public function firstOfType(string $type): ?AbstractNode
    {
        foreach ($this->getNodes() as $node) {
            if ($node instanceof $type) {
                return $node;
            }
        }

        return null;
    }

    /**
     * Tests if the document contains any node of the provided type.
     *
     * @param  string  $type The desired type.
     */
    public function hasAnyOfType(string $type): bool
    {
        foreach ($this->getNodes() as $node) {
            if ($node instanceof $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * Locates the last instance of the provided node type in the document.
     *
     * @param  string  $type The node type.
     */
    public function lastOfType(string $type): ?AbstractNode
    {
        return $this->allOfType($type)->last();
    }

    /**
     * Locates all instances of the provided node pattern within the document.
     *
     * Pattern searches exclude instances of `LiteralNode`.
     *
     * @param  string[]  $pattern The desired pattern.
     */
    public function findNodePattern(array $pattern): Collection
    {
        $nodeSearch = $this->getSearchSpaceStringOnly($pattern);
        $searchLen = strlen($nodeSearch);
        $searchIndex = $this->toSearchSpace();

        $searchString = $searchIndex[0];

        $lastPos = 0;
        $positions = [];

        $rmCount = 0;

        while (($lastPos = strpos($searchString, $nodeSearch, $lastPos)) !== false) {
            $positions[] = $lastPos + $rmCount;
            $searchString = substr($searchString, 1);
            $rmCount++;
        }

        $nodeIndexes = [];

        foreach ($positions as $char) {
            $nodeIndexes[] = $searchIndex[1][$char];
        }

        $results = [];

        foreach ($nodeIndexes as $index) {
            $results[] = $this->findNodesAt($index, $searchLen);
        }

        return collect($results);
    }

    /**
     * Retrieves the desired number of nodes at the provided start index, without considering literal node instances.
     *
     * @internal
     *
     * @param  int  $index The start index.
     * @param  int  $withoutLiteralCount The number of nodes to retrieve, not counting literals.
     */
    public function findNodesAt(int $index, int $withoutLiteralCount): NodeCollection
    {
        $results = [];

        $doCollect = false;
        $collectedCount = 0;
        foreach ($this->getNodes() as $node) {
            if ($node->index == $index) {
                $doCollect = true;
            }
            if ($doCollect) {
                $results[] = $node;

                if (! $node instanceof LiteralNode) {
                    $collectedCount += 1;
                }

                if ($collectedCount == $withoutLiteralCount) {
                    break;
                }
            }
        }

        return new NodeCollection($results);
    }

    private function getSearchSpaceStringOnly(array $classes): string
    {
        $search = '';

        foreach ($classes as $class) {
            if ($class == CommentNode::class) {
                $search .= 'C';
            } elseif ($class == DirectiveNode::class) {
                $search .= 'D';
            } elseif ($class == EchoNode::class) {
                $search .= 'E';
            } elseif ($class == PhpBlockNode::class) {
                $search .= 'B';
            } elseif ($class == PhpTagNode::class) {
                $search .= 'T';
            }
        }

        return $search;
    }

    /**
     * Generates a node search space for the provided list of nodes.
     *
     * @internal
     *
     * @param  AbstractNode[]  $nodes The nodes.
     */
    public function getNodeSearchSpace(array $nodes): array
    {
        $space = '';
        $charNum = 0;

        $index = [];

        foreach ($nodes as $node) {
            if ($node instanceof LiteralNode) {
                continue;
            }

            $char = '';

            if ($node instanceof CommentNode) {
                $char = 'C';
            } elseif ($node instanceof DirectiveNode) {
                $char = 'D';
            } elseif ($node instanceof EchoNode) {
                $char = 'E';
            } elseif ($node instanceof PhpBlockNode) {
                $char = 'B';
            } elseif ($node instanceof PhpTagNode) {
                $char = 'T';
            }

            $index[$charNum] = $node->index;
            $space .= $char;
            $charNum += 1;
        }

        return [
            $space, $index,
        ];
    }

    private function toSearchSpace(): array
    {
        return $this->getNodeSearchSpace($this->getNodes()->all());
    }
}
