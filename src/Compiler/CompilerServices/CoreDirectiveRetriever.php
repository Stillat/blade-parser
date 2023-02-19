<?php

namespace Stillat\BladeParser\Compiler\CompilerServices;

use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Stillat\BladeParser\Compiler\Attributes\ArgumentRequirement;
use Stillat\BladeParser\Compiler\Attributes\CompilesDirective;
use Stillat\BladeParser\Compiler\Attributes\StructureType;
use Stillat\BladeParser\Compiler\Compiler;
use Stillat\BladeParser\Parser\CoreDirectives;

class CoreDirectiveRetriever
{
    /**
     * @throws ReflectionException
     */
    public function __construct()
    {
        $this->analyzeCompiler();
    }

    protected static ?CoreDirectiveRetriever $instance = null;

    protected array $requiresArguments = [];

    protected array $mustNotHaveArguments = [];

    protected array $optionalArguments = [];

    protected array $directiveNames = [];

    protected array $requiresOpen = [];

    protected array $debugDirectives = [];

    protected array $includesDirectives = [];

    public static function instance(): CoreDirectiveRetriever
    {
        if (self::$instance == null) {
            self::$instance = new CoreDirectiveRetriever();
        }

        return self::$instance;
    }

    /**
     * @throws ReflectionException
     */
    protected function analyzeCompiler(): void
    {
        $this->requiresArguments = [];
        $this->mustNotHaveArguments = [];
        $this->optionalArguments = [];

        $reflectionClass = new ReflectionClass(Compiler::class);
        $methods = $reflectionClass->getMethods();

        foreach ($methods as $method) {
            $reflectionMethod = new ReflectionMethod(Compiler::class, $method->getName());
            $attributes = $reflectionMethod->getAttributes(CompilesDirective::class);

            if (count($attributes) == 0) {
                continue;
            }
            $directiveName = $method->getName();

            if (Str::startsWith($directiveName, 'compile')) {
                $directiveName = mb_substr($directiveName, 7);
            }

            $directiveName = StringUtilities::lcfirst($directiveName);

            $this->directiveNames[] = $directiveName;

            /** @var CompilesDirective $attribute */
            $attribute = $attributes[0]->newInstance();

            if ($attribute->parameterRequirement == ArgumentRequirement::Required) {
                $this->requiresArguments[] = $directiveName;
            } elseif ($attribute->parameterRequirement == ArgumentRequirement::NoArguments) {
                $this->mustNotHaveArguments[] = $directiveName;
            } elseif ($attribute->parameterRequirement == ArgumentRequirement::Optional) {
                $this->optionalArguments[] = $directiveName;
            }

            if ($attribute->structureType == StructureType::Terminator || $attribute->structureType == StructureType::Mixed) {
                $this->requiresOpen[] = $directiveName;
            } elseif ($attribute->structureType == StructureType::Debug) {
                $this->debugDirectives[] = $directiveName;
            } elseif ($attribute->structureType == StructureType::Include) {
                $this->includesDirectives[] = $directiveName;
            }
        }
    }

    /**
     * Retrieves a list of directives that include other views.
     *
     * @return string[]
     */
    public function getIncludeDirectiveNames(): array
    {
        return $this->includesDirectives;
    }

    /**
     * Retrieves a list of debug directive names.
     *
     * @return string[]
     */
    public function getDebugDirectiveNames(): array
    {
        return $this->debugDirectives;
    }

    /**
     * Retrieves a list of directives that require an opening directive.
     *
     * @return string[]
     */
    public function getDirectivesRequiringOpen(): array
    {
        return $this->requiresOpen;
    }

    /**
     * Retrieves a list of all directives supported by the compiler.
     *
     * @return string[]
     */
    public function getDirectiveNames(): array
    {
        return array_merge(CoreDirectives::BLADE_STRUCTURES, $this->directiveNames);
    }

    /**
     * Retrieves a list of all directives, excluding parser structure directives.
     *
     * @return string[]
     */
    public function getNonStructureDirectiveNames(): array
    {
        return $this->directiveNames;
    }

    /**
     * Retrieves a list of all directives requiring arguments.
     *
     * @return string[]
     */
    public function getDirectivesRequiringArguments(): array
    {
        return $this->requiresArguments;
    }

    /**
     * Retrieves a list of directives that must not have arguments.
     *
     * @return string[]
     */
    public function getDirectivesThatMustNotHaveArguments(): array
    {
        return array_merge(CoreDirectives::BLADE_STRUCTURES, $this->mustNotHaveArguments);
    }

    /**
     * Retrieves a list of directives that accept optional arguments.
     *
     * @return string[]
     */
    public function getDirectivesWithOptionalArguments(): array
    {
        return $this->optionalArguments;
    }
}
