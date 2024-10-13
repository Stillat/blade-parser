<?php

namespace Stillat\BladeParser\Nodes;

use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Document\NodeUtilities\QueriesGeneralNodes;
use Stillat\BladeParser\Errors\Exceptions\InvalidCastException;
use Stillat\BladeParser\Nodes\Fragments\FragmentPosition;
use Stillat\BladeParser\Nodes\Structures\BaseStructureNode;

abstract class AbstractNode extends BaseNode
{
    use QueriesGeneralNodes;

    protected ?Document $referenceDocument = null;

    public ?AbstractNode $parent = null;

    public bool $isStructure = false;

    public ?BaseStructureNode $structure = null;

    /**
     * @var AbstractNode[]
     */
    public array $childNodes = [];

    public FragmentPosition $fragmentPosition = FragmentPosition::Unknown;

    public ?AbstractNode $previousNode = null;

    public ?AbstractNode $nextNode = null;

    public bool $hasWhitespaceOnLeft = false;

    public bool $hasWhitespaceToRight = false;

    private function guardCast(string $target): void
    {
        if ($this instanceof $target) {
            return;
        }

        $currentClass = get_class();
        $message = "Could not convert [{$currentClass}] to [{$target}]";

        throw new InvalidCastException($message);
    }

    public function asEcho(): EchoNode
    {
        $this->guardCast(EchoNode::class);

        return $this;
    }

    public function asLiteral(): LiteralNode
    {
        $this->guardCast(LiteralNode::class);

        return $this;
    }

    public function asComment(): CommentNode
    {
        $this->guardCast(CommentNode::class);

        return $this;
    }

    public function asPhpBlock(): PhpBlockNode
    {
        $this->guardCast(PhpBlockNode::class);

        return $this;
    }

    public function asPhpTag(): PhpTagNode
    {
        $this->guardCast(PhpTagNode::class);

        return $this;
    }

    public function asVerbatim(): VerbatimNode
    {
        $this->guardCast(VerbatimNode::class);

        return $this;
    }

    public function asDirective(): DirectiveNode
    {
        $this->guardCast(DirectiveNode::class);

        return $this;
    }

    public function hasWhitespaceOnLeft(): bool
    {
        return $this->hasWhitespaceOnLeft;
    }

    public function hasWhitespaceOnRight(): bool
    {
        return $this->hasWhitespaceToRight;
    }

    public function getNextNode(): ?AbstractNode
    {
        return $this->nextNode;
    }

    public function hasNextNode(): bool
    {
        return $this->nextNode != null;
    }

    public function hasPreviousNode(): bool
    {
        return $this->previousNode != null;
    }

    public function getPreviousNode(): ?AbstractNode
    {
        return $this->previousNode;
    }

    public function setDocument(?Document $document): void
    {
        $this->referenceDocument = $document;
    }

    public function getDocument(): ?Document
    {
        return $this->referenceDocument;
    }

    public function hasDocument(): bool
    {
        return $this->referenceDocument != null;
    }

    /**
     * Returns the child nodes.
     */
    public function getNodes(): NodeCollection
    {
        return new NodeCollection($this->childNodes);
    }

    public function getNode(): AbstractNode
    {
        return $this;
    }

    public function getParent(): ?AbstractNode
    {
        return $this->parent;
    }

    public function hasParent(): bool
    {
        return $this->parent != null;
    }

    public function hasStructure(): bool
    {
        return $this->structure != null;
    }

    public function getRootNodes(): NodeCollection
    {
        return $this->getDirectChildren();
    }

    public function resolveStructures(): void {}

    public function getDirectChildren(): NodeCollection
    {
        return $this->getNodes()->where(function (AbstractNode $node) {
            return $node->parent === $this;
        })->values();
    }

    public function getAllParentNodes(): NodeCollection
    {
        return $this->getAllParentNodesForNode($this);
    }

    public function hasParentOfType(string $type): bool
    {
        return $this->getNodeHasParentOfType($this, $type);
    }

    public function hasConditionParent(): bool
    {
        return $this->getNodeHasConditionParent($this);
    }

    public function hasForElseParent(): bool
    {
        return $this->getNodeHasForElseParent($this);
    }

    public function hasSwitchParent(): bool
    {
        return $this->getNodeHasSwitchParent($this);
    }

    public function isInHtmlParameter(): bool
    {
        return $this->fragmentPosition == FragmentPosition::InsideParameter;
    }

    public function isBetweenHtmlFragments(): bool
    {
        if ($this instanceof LiteralNode) {
            return false;
        }

        return $this->fragmentPosition == FragmentPosition::Unknown;
    }

    public function isInHtmlTagName(): bool
    {
        return $this->fragmentPosition == FragmentPosition::InsideFragmentName;
    }

    public function isInHtmlTagContent(): bool
    {
        return $this->fragmentPosition == FragmentPosition::InsideFragment;
    }

    /**
     * Indicates if the current node is in a dirty state.
     */
    private bool $isDirty = false;

    /**
     * Sets the node's internal dirty state.
     *
     * @param  bool  $isDirty  The dirty status.
     */
    protected function setIsDirty(bool $isDirty = true): void
    {
        $this->isDirty = $isDirty;
    }

    /**
     * Returns a value indicating if the current node is in a dirty state.
     */
    public function isDirty(): bool
    {
        return $this->isDirty;
    }

    abstract public function clone();

    /**
     * Returns a string representation of the node.
     */
    public function toString(): string
    {
        return (string) $this;
    }
}
