<?php

namespace Stillat\BladeParser\Nodes;

use Illuminate\Support\Collection;
use Stillat\BladeParser\Document\Structures\DirectiveClosingAnalyzer;
use Stillat\BladeParser\Nodes\Concerns\ContainsDocumentText;
use Stillat\BladeParser\Nodes\Concerns\ProvidesAccessToResolvedStructures;

class DirectiveNode extends AbstractNode
{
    use ContainsDocumentText, ProvidesAccessToResolvedStructures;

    /**
     * The directive's source content, including any arguments.
     */
    public string $sourceContent = '';

    /**
     * The directive's arguments.
     */
    public ?ArgumentGroupNode $arguments = null;

    /**
     * Indicates if the current directive is a closing directive.
     */
    public bool $isClosingDirective = false;

    /**
     * The position of the directive's name, if available.
     */
    public ?Position $directiveNamePosition = null;

    /**
     * A reference to the directive that begins the directive pair, if any.
     */
    public ?DirectiveNode $isOpenedBy = null;

    /**
     * A reference to the directive that closes the directive pair, if any.
     */
    public ?DirectiveNode $isClosedBy = null;

    /**
     * Indicates if the directive is considered to be a "condition-like" directive.
     *
     * Examples of this are @can, @auth, @env, etc.
     */
    public bool $isConditionDirective = false;

    /**
     * Indicates if the directive requires a closing directive during structural analysis.
     */
    public bool $conditionRequiresClose = false;

    /**
     * A normalized conditional structure name used during structural analysis.
     *
     * Directives such as @can, @auth, and similar would result in the value "if", for instance.
     */
    public string $conditionStructureName = '';

    /**
     * Returns the directive's conditional structure name, if available.
     */
    public function getConditionStructureName(): string
    {
        return $this->conditionStructureName;
    }

    /**
     * Returns a value indicating if the directive requires a closing
     * conditional directive during structural analysis.
     */
    public function getConditionRequiresClose(): bool
    {
        return $this->conditionRequiresClose;
    }

    /**
     * Returns a value indicating if the directive is "condition-like".
     */
    public function getIsConditionDirective(): bool
    {
        return $this->isConditionDirective;
    }

    /**
     * Returns a value indicating if the directive arguments
     * start on the same line as the directive name.
     */
    public function argumentsBeginOnSameLine(): bool
    {
        if (! $this->hasArguments()) {
            return true;
        }

        return $this->position->startLine == $this->arguments->position->startLine;
    }

    /**
     * Returns the total number of lines the directive spans, including arguments.
     */
    public function getSpannedLineCount(): int
    {
        if ($this->position == null) {
            return 1;
        }

        return $this->position->endLine - $this->position->startLine + 1;
    }

    /**
     * Returns a value indicating if the directive spans multiple lines, including arguments.
     */
    public function spansMultipleLines(): bool
    {
        return $this->getSpannedLineCount() > 1;
    }

    /**
     * Returns the number of characters between the directive name and it's arguments.
     */
    public function getArgumentsDistance(): int
    {
        if (! $this->hasArguments()) {
            return 0;
        }
        if ($this->directiveNamePosition == null) {
            return 0;
        }

        $dist = $this->arguments->position->startOffset - $this->directiveNamePosition->endOffset - 1;

        if ($dist < 0) {
            return 0;
        }

        return $dist;
    }

    /**
     * Gets a list of all chained closing directives.
     *
     * @return Collection<int, DirectiveNode>
     */
    public function getChainedClosingDirectives(): Collection
    {
        if ($this->isClosedBy == null) {
            return collect();
        }

        $directives = [];

        $closingDirective = $this->isClosedBy;

        while ($closingDirective != null) {
            $directives[] = $closingDirective;
            $closingDirective = $closingDirective->isClosedBy;
        }

        return collect($directives);
    }

    /**
     * Returns the furthest opening directive node that does not have its own parent.
     *
     * An example would be an @endif node returning the first @if within the conditional chain.
     */
    public function getRootOpeningDirective(): ?DirectiveNode
    {
        if ($this->isOpenedBy == null) {
            return null;
        }

        $rootOpeningDirective = $this->isOpenedBy;
        $mostRecent = $this->isOpenedBy;

        while ($rootOpeningDirective != null) {
            if ($rootOpeningDirective->isOpenedBy != null) {
                $mostRecent = $rootOpeningDirective->isOpenedBy;
            }

            $rootOpeningDirective = $rootOpeningDirective->isOpenedBy;
        }

        return $mostRecent;
    }

    /**
     * Returns the furthest closing node from the current node.
     *
     * An example would be an @if directive returning the final @endif.
     */
    public function getFinalClosingDirective(): ?DirectiveNode
    {
        if ($this->isClosedBy == null) {
            return null;
        }

        $ultimateClosingDirective = $this->isClosedBy;
        $mostRecent = $this->isClosedBy;

        while ($ultimateClosingDirective != null) {
            if ($ultimateClosingDirective->isClosedBy != null) {
                $mostRecent = $ultimateClosingDirective->isClosedBy;
            }

            $ultimateClosingDirective = $ultimateClosingDirective->isClosedBy;
        }

        return $mostRecent;
    }

    /**
     * Returns a value indicating if the directive contains arguments.
     */
    public function hasArguments(): bool
    {
        return $this->arguments != null;
    }

    /**
     * Updates the directive's name.
     *
     * This method does not verify if the provided name is recognized as a directive.
     *
     * @param  string  $name The new directive name.
     */
    public function setName(string $name): void
    {
        $this->setIsDirty();
        $this->content = trim($name);
        $this->updateSourceContent();
        DirectiveClosingAnalyzer::analyze($this);
    }

    public function setArguments(string $args): void
    {
        $this->setIsDirty();

        if ($this->arguments == null) {
            $this->arguments = new ArgumentGroupNode($this);
        }

        $this->arguments->setContent($args);
        $this->updateSourceContent();
    }

    /**
     * @internal
     */
    public function updateSourceContent(): void
    {
        $this->sourceContent = '@'.$this->content;

        if ($this->arguments != null) {
            $this->sourceContent .= ' '.(string) $this->arguments;
        }
    }

    /**
     * Removes the arguments from the directive.
     */
    public function removeArguments(): void
    {
        $this->setIsDirty();
        $this->arguments = null;
        $this->updateSourceContent();
    }

    /**
     * Returns the directive's argument's inner content, if available.
     */
    public function getValue(): ?string
    {
        return $this->arguments?->innerContent;
    }

    public function clone(): DirectiveNode
    {
        $directive = new DirectiveNode();
        $this->copyBasicDetailsTo($directive);

        $directive->isClosingDirective = $this->isClosingDirective;
        $directive->sourceContent = $this->sourceContent;
        $directive->arguments = $this->arguments?->clone($directive);

        return $directive;
    }

    public function __toString(): string
    {
        return $this->sourceContent;
    }
}
