<?php

namespace Stillat\BladeParser\Compiler\Transformers;

use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Nodes\AbstractNode;

abstract class NodeTransformer
{
    private ?AbstractNode $skipNode = null;

    public function transformDocument(Document $document): string
    {
        return $this->transformNodes($document->getNodeArray());
    }

    public function transformNodes(array $nodes): string
    {
        $result = '';

        foreach ($nodes as $node) {
            if ($this->skipNode != null) {
                if ($this->skipNode == $node) {
                    $this->skipNode = null;
                }

                continue;
            }

            $nodeResult = $this->transformNode($node);

            if ($nodeResult !== null) {
                $result .= $nodeResult;

                continue;
            }

            $result .= (string) $node;
        }

        return $result;
    }

    public function skipToNode(AbstractNode $node): void
    {
        $this->skipNode = $node;
    }

    abstract public function transformNode($node): ?string;
}
