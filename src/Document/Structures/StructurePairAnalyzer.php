<?php

namespace Stillat\BladeParser\Document\Structures;

use Illuminate\Support\Str;
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Document\Structures\Concerns\ConstructsConditions;
use Stillat\BladeParser\Document\Structures\Concerns\ConstructsForElse;
use Stillat\BladeParser\Document\Structures\Concerns\ConstructsSwitchStatements;
use Stillat\BladeParser\Document\Structures\Concerns\ManagesConditionMetaData;
use Stillat\BladeParser\Document\Structures\Concerns\ManagesDirectiveIndexes;
use Stillat\BladeParser\Document\Structures\Concerns\PairsComponentTags;
use Stillat\BladeParser\Document\Structures\Concerns\PairsConditionalStructures;
use Stillat\BladeParser\Document\Structures\Concerns\ResolvesStructureDocuments;
use Stillat\BladeParser\Document\Structures\Concerns\ScansForClosingPairs;
use Stillat\BladeParser\Nodes\DirectiveNode;

class StructurePairAnalyzer
{
    use ScansForClosingPairs, ManagesDirectiveIndexes, ManagesConditionMetaData,
        PairsConditionalStructures, ConstructsConditions, ConstructsForElse,
        ConstructsSwitchStatements, ResolvesStructureDocuments, PairsComponentTags;

    private Document $document;

    private array $possibleClosingDirectives = [];

    private array $speculativeConditions = [];

    private int $stackCount = 0;

    private array $closingDirectiveIndex = [];

    private array $closingDirectiveIndexCount = [];

    private array $closingDirectiveNames = [];

    private array $openDirectiveIndexCount = [];

    private array $abandonedConditionNodes = [];

    private ?DirectiveNode $parentNode = null;

    public function __construct(Document $document)
    {
        $this->document = $document;
        $this->constructClosingCandidates();
        $this->constructDynamicConditions();
    }

    private function resetState(): void
    {
        $this->stackCount = 0;
        $this->closingDirectiveIndex = [];
        $this->closingDirectiveIndexCount = [];
        $this->closingDirectiveNames = [];
        $this->openDirectiveIndexCount = [];
        $this->abandonedConditionNodes = [];
        $this->parentNode = null;
    }

    public function getSimplifiedStructureName(DirectiveNode $node): string
    {
        $checkName = mb_strtolower($node->content);

        if (Str::startsWith($checkName, 'end') && mb_strlen($checkName) > 3) {
            $checkName = mb_substr($checkName, 3);

            if (array_key_exists($checkName, $this->speculativeConditions)) {
                return 'if';
            }

            return $checkName;
        }

        if (Str::startsWith($checkName, 'else') && mb_strlen($checkName) > 4) {
            $checkName = mb_substr($checkName, 4);

            if (array_key_exists($checkName, $this->speculativeConditions)) {
                return 'elseif';
            }

            return $checkName;
        }

        if (array_key_exists($checkName, $this->speculativeConditions)) {
            return 'if';
        }

        return $checkName;
    }

    private function constructClosingCandidates(): void
    {
        foreach ($this->document->getDirectiveNames() as $directiveName) {
            $checkName = mb_strtolower($directiveName);

            if (Str::startsWith($checkName, 'end')) {
                $this->possibleClosingDirectives[$checkName] = 1;
            }
        }
    }

    public static function getDefaultSpeculativeConditions(): array
    {
        return [
            'unless',
            'sectionMissing',
            'hasSection',
            'can',
            'auth',
            'env',
            'isset',
            'guest',
            'cannot',
            'canany',
            'hasSection',
            'production',
            'if',
        ];
    }

    private function constructDynamicConditions(): void
    {
        $directiveNames = collect($this->document->getDirectiveNames())->map(function ($name) {
            return mb_strtolower($name);
        })->flip()->all();

        $speculativeConditions = self::getDefaultSpeculativeConditions();

        foreach ($directiveNames as $directiveName => $v) {
            $elseDirective = 'else'.$directiveName;
            $endDirective = 'end'.$directiveName;

            if (array_key_exists($elseDirective, $directiveNames) && array_key_exists($endDirective, $directiveNames)) {
                $speculativeConditions[] = $directiveName;
            }
        }

        $this->speculativeConditions = collect($speculativeConditions)->map(function ($name) {
            return mb_strtolower($name);
        })->flip()->all();
    }

    public function associate(): void
    {
        $this->resetState();
        /** @var DirectiveStackItem[] $nodeStack */
        $nodeStack = [];

        $stackItem = new DirectiveStackItem();
        $stackItem->documentNodes = $this->document->getNodes()->all();

        $nodeStack[] = $stackItem;

        while (count($nodeStack) > 0) {
            $details = array_pop($nodeStack);

            if ($details == null) {
                continue;
            }
            $nodes = $details->documentNodes;
            $this->parentNode = $details->refParent;

            $this->stackCount += 1;
            $this->buildCloseIndex($nodes);

            $this->pairConditions($nodes);

            foreach ($nodes as $node) {
                if ($node instanceof DirectiveNode && $this->canClose($this->getSimplifiedStructureName($node))) {
                    if ($this->isConditionalStructure($node)) {
                        continue;
                    }

                    $scanFor = $this->getScanForList($node);
                    $this->findClosingPair($nodes, $node, $scanFor);
                }
            }
        }

        $nodes = $this->document->getNodes()->all();
        $this->pairComponentTags($nodes);

        (new RelationshipAnalyzer($this->document))->analyze();

        $this->constructConditions($nodes);
        $this->constructSwitchStatements($nodes);
        $this->constructForElse($nodes);
        $this->resolveStructureDocuments($nodes);

        $this->resetState();
    }
}
