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

    public function getCustomDirectives(): array
    {
        return $this->compiler->getCustomDirectives();
    }

    /**
     * Retrieves the Blade precompilers.
     *
     *
     * @throws ReflectionException
     */
    public function getPrecompilers(): array
    {
        if (! $this->reflection->hasProperty('precompilers')) {
            return [];
        }

        $property = $this->reflection->getProperty('precompilers');

        return $property->getValue($this->compiler);
    }

    private function safeGetValue(string $methodToTry, string $backingProperty, mixed $default)
    {
        if (! method_exists($this->compiler, $methodToTry)) {
            if ($this->reflection->hasProperty($backingProperty)) {
                $property = $this->reflection->getProperty($backingProperty);

                return $property->getValue($this->compiler);
            }

            return $default;
        }

        return $this->compiler->{$methodToTry}();
    }

    public function getClassComponentAliases()
    {
        return $this->safeGetValue('getClassComponentAliases', 'classComponentAliases', []);
    }

    public function getAnonymousComponentNamespaces()
    {
        return $this->safeGetValue('getAnonymousComponentNamespaces', 'anonymousComponentNamespaces', []);
    }

    public function getClassComponentNamespaces()
    {
        return $this->safeGetValue('getClassComponentNamespaces', 'classComponentNamespaces', []);
    }

    public function getAnonymousComponentPaths()
    {
        return $this->safeGetValue('getAnonymousComponentPaths', 'anonymousComponentPaths', []);
    }

    public function getConditions()
    {
        if (! $this->reflection->hasProperty('conditions')) {
            return [];
        }

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
        if (! $this->reflection->hasProperty('echoFormat')) {
            return 'e(%s)';
        }

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
        if (! $this->reflection->hasProperty('compilesComponentTags')) {
            return true;
        }

        $property = $this->reflection->getProperty('compilesComponentTags');

        return $property->getValue($this->compiler);
    }

    public function getEchoHandlers(): array
    {
        if (! $this->reflection->hasProperty('echoHandlers')) {
            return [];
        }

        $property = $this->reflection->getProperty('echoHandlers');

        return $property->getValue($this->compiler);
    }

    public function getExtensions(): array
    {
        return $this->safeGetValue('getExtensions', 'extensions', []);
    }
}
