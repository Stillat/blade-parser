<?php

namespace Stillat\BladeParser\Support;

use Illuminate\View\Compilers\BladeCompiler;
use ReflectionClass;
use ReflectionException;

class BladeCompilerDetailsFetcher
{
    private BladeCompiler $compiler;

    private ReflectionClass $reflection;

    public function __construct(BladeCompiler $compiler)
    {
        $this->compiler = $compiler;
        $this->reflection = new ReflectionClass(BladeCompiler::class);
    }

    /**
     * Retrieves the Blade precompilers.
     *
     *
     * @throws ReflectionException
     */
    public function getPrecompilers(): array
    {
        $property = $this->reflection->getProperty('precompilers');

        return $property->getValue($this->compiler);
    }

    public function getClassComponentAliases()
    {
        return $this->compiler->getClassComponentAliases();
    }

    public function getAnonymousComponentNamespaces()
    {
        return $this->compiler->getAnonymousComponentNamespaces();
    }

    public function getClassComponentNamespaces()
    {
        return $this->compiler->getClassComponentNamespaces();
    }

    public function getAnonymousComponentPaths()
    {
        return $this->compiler->getAnonymousComponentPaths();
    }

    public function getConditions()
    {
        $property = $this->reflection->getProperty('conditions');

        return $property->getValue($this->compiler);
    }

    /**
     * Returns the configured echo format.
     *
     *
     * @throws ReflectionException
     */
    public function getEchoFormat(): string
    {
        $property = $this->reflection->getProperty('echoFormat');

        return $property->getValue($this->compiler);
    }

    /**
     * Returns whether to compile component tags.
     *
     *
     * @throws ReflectionException
     */
    public function getWithoutComponentTags(): bool
    {
        $property = $this->reflection->getProperty('compilesComponentTags');

        return $property->getValue($this->compiler);
    }

    public function getEchoHandlers(): array
    {
        $property = $this->reflection->getProperty('echoHandlers');

        return $property->getValue($this->compiler);
    }

    public function getExtensions(): array
    {
        return $this->compiler->getExtensions();
    }
}
