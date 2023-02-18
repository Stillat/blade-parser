<?php

namespace Stillat\BladeParser\Compiler;

use Illuminate\Support\Str;
use Stillat\BladeParser\Contracts\CustomComponentTagCompiler;
use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Parser\DocumentParser;

class ComponentTagCompiler
{
    protected ComponentNodeCompiler $compiler;

    protected DocumentParser $parser;

    protected bool $compileCoreComponents = true;

    /**
     * @var CustomComponentTagCompiler[]
     */
    protected array $customCompilers = [];

    public function __construct(ComponentNodeCompiler $compiler, DocumentParser $parser)
    {
        $this->compiler = $compiler;
        $this->parser = $parser;
    }

    public function registerCustomCompiler(string $tagName, CustomComponentTagCompiler $compiler): ComponentTagCompiler
    {
        $this->customCompilers[$tagName] = $compiler;
        $this->parser->registerCustomComponentTag($tagName);

        return $this;
    }

    public function setCompileCoreComponents(bool $compileCoreComponents): ComponentTagCompiler
    {
        $this->compileCoreComponents = $compileCoreComponents;

        return $this;
    }

    public function getCompileCoreComponents(): bool
    {
        return $this->compileCoreComponents;
    }

    public function withoutCoreComponents(): ComponentTagCompiler
    {
        return $this->setCompileCoreComponents(false);
    }

    public function registerCustomComponentTag(string $tagName): ComponentTagCompiler
    {
        $this->parser->registerCustomComponentTag($tagName);

        return $this;
    }

    public function getComponentNodeCompiler(): ComponentNodeCompiler
    {
        return $this->compiler;
    }

    public function setAliases(array $aliases): void
    {
        $this->compiler->setAliases($aliases);
    }

    public function setNamespaces(array $namespaces): void
    {
        $this->compiler->setNamespaces($namespaces);
    }

    public function setAnonymousComponentPaths(array $paths): void
    {
        $this->compiler->setAnonymousComponentPaths($paths);
    }

    public function setAnonymousComponentNamespaces(array $namespaces): void
    {
        $this->compiler->setAnonymousComponentNamespaces($namespaces);
    }

    public function guessClassName(string $component)
    {
        return $this->compiler->guessClassName($component);
    }

    private function getComponentPrefixes(): array
    {
        $prefixes = ['<x-', '<x:'];

        foreach ($this->parser->getCustomComponentTags() as $tagName) {
            $prefixes[] = '<'.$tagName.'-';
            $prefixes[] = '<'.$tagName.':';
        }

        return $prefixes;
    }

    public function compile(string $value): string
    {
        // Return early if there is no chance of finding components.
        if (! Str::contains($value, $this->getComponentPrefixes())) {
            return $value;
        }

        $nodes = $this->parser->parse($value);

        if (! $this->parser->hasAnyComponents()) {
            return $value;
        }

        return $this->compileNodes($nodes);
    }

    public function compileNodes(array $nodes): string
    {
        return collect($nodes)->map(function ($node) {
            if ($node instanceof ComponentNode) {
                if ($node->isCustomComponent && array_key_exists($node->componentPrefix, $this->customCompilers)) {
                    $result = $this->customCompilers[$node->componentPrefix]->compile($node);

                    if ($result === null) {
                        return $this->compiler->compileNode($node);
                    }

                    return $result;
                } else {
                    if ($this->compileCoreComponents) {
                        return $this->compiler->compileNode($node);
                    } else {
                        return $node->content;
                    }
                }
            }

            if ($node instanceof DirectiveNode) {
                return $node->sourceContent;
            }

            return $node->content;
        })->implode('');
    }

    public function compileTags(string $value): string
    {
        return $this->compile($value);
    }
}
