<?php

namespace Stillat\BladeParser\Compiler\Transformers;

use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\PhpBlockNode;
use Stillat\BladeParser\Nodes\PhpTagNode;
use Stillat\BladeParser\Nodes\VerbatimNode;

/**
 * Class RawTransformer
 *
 * Responsible for emulating the Core Laravel Blade Compiler's
 *
 * @__raw_block_0__@ method for handling verbatim and PHP
 * regions. This step isn't required for compiling any
 * documents, but is important for providing backwards
 * compatibility with any existing pre-parsers that
 * may incorrectly perform replacements on them.
 *
 * @since 1.0.0
 */
class RawTransformer
{
    protected array $rawBlocks = [];

    protected array $phpTags = [];

    protected int $currentRawBlock = 0;

    protected function resetState(): void
    {
        $this->rawBlocks = [];
        $this->phpTags = [];
        $this->currentRawBlock = 0;
    }

    public function getPhpTags(): array
    {
        return $this->phpTags;
    }

    public function getNextPhpTag(): ?PhpTagNode
    {
        return array_shift($this->phpTags);
    }

    /**
     * Transforms an array of nodes into a string with raw blocks.
     *
     * @param  array  $nodes The nodes
     */
    public function transform(array $nodes): string
    {
        $this->resetState();

        return collect($nodes)->map(function ($node) {
            if ($node instanceof DirectiveNode) {
                return $node->sourceContent;
            }

            if ($node instanceof VerbatimNode || $node instanceof PhpBlockNode) {
                $replace = '@__raw_block_'.$this->currentRawBlock.'__@';
                $this->rawBlocks[$replace] = $node;
                $this->currentRawBlock += 1;

                return $replace;
            } elseif ($node instanceof PhpTagNode) {
                $this->phpTags[] = $node;
            }

            return $node->content;
        })->implode('');
    }

    /**
     * Replaces raw placeholders within a previously transformed string.
     *
     * @param  string  $document The previously transformed string.
     */
    public function reverseTransformation(string $document): string
    {
        foreach ($this->rawBlocks as $replace => $node) {
            $document = str_replace($replace, $node->content, $document);
        }

        return $document;
    }
}
