<?php

namespace Stillat\BladeParser\Compiler;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Str;
use Illuminate\View\AnonymousComponent;
use Illuminate\View\DynamicComponent;
use Illuminate\View\ViewFinderInterface;
use InvalidArgumentException;
use ReflectionClass;
use Stillat\BladeParser\Compiler\CompilerServices\StringUtilities;
use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Nodes\Components\ParameterNode;
use Stillat\BladeParser\Nodes\Components\ParameterType;
use Stillat\BladeParser\Parser\DocumentParser;

class ComponentNodeCompiler
{
    protected array $aliases = [];

    protected array $namespaces = [];

    protected array $boundAttributes = [];

    protected array $anonymousComponentPaths = [];

    protected array $anonymousComponentNamespaces = [];

    protected bool $throwExceptionOnUnknownComponentClass = true;

    protected array $failedComponents = [];

    public function setAliases(array $aliases): void
    {
        $this->aliases = $aliases;
    }

    public function getFailedComponents(): array
    {
        return $this->failedComponents;
    }

    public function clearFailedComponents(): void
    {
        $this->failedComponents = [];
    }

    public function setThrowExceptionOnUnknownComponentClass(bool $doThrow): void
    {
        $this->throwExceptionOnUnknownComponentClass = $doThrow;
    }

    public function getThrowExceptionOnUnknownComponentClass(): bool
    {
        return $this->throwExceptionOnUnknownComponentClass;
    }

    public function setNamespaces(array $namespaces): void
    {
        $this->namespaces = $namespaces;
    }

    public function setAnonymousComponentPaths(array $paths): void
    {
        $this->anonymousComponentPaths = $paths;
    }

    public function setAnonymousComponentNamespaces(array $namespaces): void
    {
        $this->anonymousComponentNamespaces = $namespaces;
    }

    protected function resetState()
    {
        $this->boundAttributes = [];
    }

    public function compileNode(ComponentNode $component): string
    {
        $this->resetState();

        if ($component->isSelfClosing) {
            return $this->compileSelfClosingTag($component);
        } elseif ($component->isClosingTag) {
            if ($component->tagName == 'slot') {
                return ' @endslot';
            }

            return ' @endComponentClass##END-COMPONENT-CLASS##';
        }

        if ($component->tagName == 'slot') {
            return $this->compileSlotComponent($component);
        }

        return $this->compileComponentString($component);
    }

    protected function compileSlotComponent(ComponentNode $componentNode): string
    {
        $name = $this->getSlotName($componentNode);

        if ($name instanceof ParameterNode) {
            if ($name->type == ParameterType::DynamicVariable || $name->type == ParameterType::ShorthandDynamicVariable) {
                $name = $name->value;
            } else {
                $name = "'".$name->value."'";
            }
        } else {
            $name = "'".$name."'";
        }

        $attributes = $this->toAttributeArray($componentNode);

        if (! Str::contains($componentNode->name, ':')) {
            unset($attributes['name']);
        }

        return " @slot({$name}, null, [".$this->attributesToString($attributes).']) ';
    }

    protected function getSlotName(ComponentNode $componentNode): string|ParameterNode
    {
        if (Str::contains($componentNode->name, ':')) {
            return Str::after($componentNode->name, ':');
        }

        $name = $componentNode->getParameter('name');

        if ($name != null) {
            return $name;
        }

        return '';
    }

    protected function compileSelfClosingTag(ComponentNode $component): string
    {
        return $this->compileComponentString($component)."\n@endComponentClass##END-COMPONENT-CLASS##";
    }

    protected function compileParameterEcho(ParameterNode $node): string
    {
        $compiler = new Compiler(new DocumentParser());
        $compiler->setCompilationTarget(CompilationTarget::ComponentParameter);

        $result = $compiler->compileString($node->value);

        if ($node->type == ParameterType::InterpolatedValue && Str::startsWith($node->value, '{{') && Str::endsWith($node->value, '}}')) {
            $result = "'".$result."'";
        }

        return $result;
    }

    protected function toAttributeArray(ComponentNode $component): array
    {
        $attributes = [];

        foreach ($component->parameters as $parameter) {
            if (Str::contains(mb_strtolower($parameter->content), '$attributes') && ($parameter->type == ParameterType::AttributeEcho || $parameter->type == ParameterType::AttributeRawEcho || $parameter->type == ParameterType::AttributeTripleEcho)) {
                $trimCount = 2;

                if ($parameter->type == ParameterType::AttributeRawEcho || $parameter->type == ParameterType::AttributeTripleEcho) {
                    $trimCount = 3;
                }

                $value = trim(mb_substr($parameter->content, $trimCount, -$trimCount));

                $attributes['attributes'] = '\Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('.$value.')';

                continue;
            }

            if ($parameter->type == ParameterType::DynamicVariable ||
                $parameter->type == ParameterType::ShorthandDynamicVariable) {
                $this->boundAttributes[$parameter->materializedName] = true;
            }

            if ($parameter->type == ParameterType::InterpolatedValue) {
                $result = $this->compileParameterEcho($parameter);

                $attributes[$parameter->materializedName] = StringUtilities::wrapInSingleQuotes($result);

                continue;
            } elseif ($parameter->type == ParameterType::Attribute) {
                $attributes[$parameter->materializedName] = 'true';

                continue;
            }

            $value = $parameter->value;

            if ($parameter->type != ParameterType::DynamicVariable &&
                $parameter->type != ParameterType::ShorthandDynamicVariable) {
                $value = StringUtilities::wrapInSingleQuotes($value);
            }

            $attributes[$parameter->materializedName] = $value;
        }

        return $attributes;
    }

    protected function compileComponentString(ComponentNode $componentNode): string
    {
        $component = $componentNode->tagName;
        $class = '';
        try {
            $class = $this->componentClass($componentNode->tagName);
        } catch (InvalidArgumentException $e) {
            if ($this->throwExceptionOnUnknownComponentClass) {
                throw $e;
            }

            $this->failedComponents[] = $componentNode;
            $class = UnresolvableComponent::class;
        }
        $attributes = $this->toAttributeArray($componentNode);
        [$data, $attributes] = $this->partitionDataAndAttributes($class, $attributes);

        $data = $data->mapWithKeys(function ($value, $key) {
            return [Str::camel($key) => $value];
        });

        // If the component doesn't exist as a class, we'll assume it's a class-less
        // component and pass the component as a view parameter to the data so it
        // can be accessed within the component and we can render out the view.
        if (! class_exists($class)) {
            $view = Str::startsWith($component, 'mail::')
                ? "\$__env->getContainer()->make(Illuminate\\View\\Factory::class)->make('{$component}')"
                : "'$class'";

            $parameters = [
                'view' => $view,
                'data' => '['.$this->attributesToString($data->all(), $escapeBound = false).']',
            ];

            $class = AnonymousComponent::class;
        } else {
            $parameters = $data->all();
        }

        return "##BEGIN-COMPONENT-CLASS##@component('{$class}', '{$component}', [".$this->attributesToString($parameters, $escapeBound = false).'])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass('.$class.'::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['.$this->attributesToString($attributes->all(), $escapeAttributes = $class !== DynamicComponent::class).']); ?>';
    }

    /**
     * Get the component class for a given component alias.
     *
     * @return string
     *
     * @throws InvalidArgumentException|BindingResolutionException
     */
    public function componentClass(string $component)
    {
        $viewFactory = Container::getInstance()->make(Factory::class);

        if (isset($this->aliases[$component])) {
            if (class_exists($alias = $this->aliases[$component])) {
                return $alias;
            }

            if ($viewFactory->exists($alias)) {
                return $alias;
            }

            throw new InvalidArgumentException(
                "Unable to locate class or view [{$alias}] for component [{$component}]."
            );
        }

        if ($class = $this->findClassByComponent($component)) {
            return $class;
        }

        if (class_exists($class = $this->guessClassName($component))) {
            return $class;
        }

        if (! is_null($guess = $this->guessAnonymousComponentUsingNamespaces($viewFactory, $component)) ||
            ! is_null($guess = $this->guessAnonymousComponentUsingPaths($viewFactory, $component))) {
            return $guess;
        }

        if (Str::startsWith($component, 'mail::')) {
            return $component;
        }

        throw new InvalidArgumentException(
            "Unable to locate a class or view for component [{$component}]."
        );
    }

    /**
     * Attempt to find an anonymous component using the registered anonymous component paths.
     *
     * @return string|null
     */
    protected function guessAnonymousComponentUsingPaths(Factory $viewFactory, string $component)
    {
        $delimiter = ViewFinderInterface::HINT_PATH_DELIMITER;

        foreach ($this->anonymousComponentPaths as $path) {
            try {
                if (str_contains($component, $delimiter) &&
                    ! str_starts_with($component, $path['prefix'].$delimiter)) {
                    continue;
                }

                $formattedComponent = str_starts_with($component, $path['prefix'].$delimiter)
                    ? Str::after($component, $delimiter)
                    : $component;

                if (! is_null($guess = match (true) {
                    $viewFactory->exists($guess = $path['prefixHash'].$delimiter.$formattedComponent) => $guess,
                    $viewFactory->exists($guess = $path['prefixHash'].$delimiter.$formattedComponent.'.index') => $guess,
                    default => null,
                })) {
                    return $guess;
                }
            } catch (InvalidArgumentException $e) {
                //
            }
        }
    }

    /**
     * Attempt to find an anonymous component using the registered anonymous component namespaces.
     *
     * @return string|null
     */
    protected function guessAnonymousComponentUsingNamespaces(Factory $viewFactory, string $component)
    {
        return collect($this->anonymousComponentNamespaces)
            ->filter(function ($directory, $prefix) use ($component) {
                return Str::startsWith($component, $prefix.'::');
            })
            ->prepend('components', $component)
            ->reduce(function ($carry, $directory, $prefix) use ($component, $viewFactory) {
                if (! is_null($carry)) {
                    return $carry;
                }

                $componentName = Str::after($component, $prefix.'::');

                if ($viewFactory->exists($view = $this->guessViewName($componentName, $directory))) {
                    return $view;
                }

                if ($viewFactory->exists($view = $this->guessViewName($componentName, $directory).'.index')) {
                    return $view;
                }
            });
    }

    /**
     * Find the class for the given component using the registered namespaces.
     *
     * @return string|null
     */
    public function findClassByComponent(string $component)
    {
        $segments = explode('::', $component);

        $prefix = $segments[0];

        if (! isset($this->namespaces[$prefix], $segments[1])) {
            return;
        }

        if (class_exists($class = $this->namespaces[$prefix].'\\'.$this->formatClassName($segments[1]))) {
            return $class;
        }
    }

    /**
     * Guess the class name for the given component.
     *
     * @return string
     */
    public function guessClassName(string $component)
    {
        $namespace = Container::getInstance()
            ->make(Application::class)
            ->getNamespace();

        $class = $this->formatClassName($component);

        return $namespace.'View\\Components\\'.$class;
    }

    /**
     * Format the class name for the given component.
     *
     * @return string
     */
    public function formatClassName(string $component)
    {
        $componentPieces = array_map(function ($componentPiece) {
            return ucfirst(Str::camel($componentPiece));
        }, explode('.', $component));

        return implode('\\', $componentPieces);
    }

    /**
     * Guess the view name for the given component.
     *
     * @param  string  $name
     * @param  string  $prefix
     * @return string
     */
    public function guessViewName($name, $prefix = 'components.')
    {
        if (! Str::endsWith($prefix, '.')) {
            $prefix .= '.';
        }

        $delimiter = ViewFinderInterface::HINT_PATH_DELIMITER;

        if (str_contains($name, $delimiter)) {
            return Str::replaceFirst($delimiter, $delimiter.$prefix, $name);
        }

        return $prefix.$name;
    }

    /**
     * Partition the data and extra attributes from the given array of attributes.
     *
     * @param  string  $class
     * @return array
     */
    public function partitionDataAndAttributes($class, array $attributes)
    {
        // If the class doesn't exist, we'll assume it is a class-less component and
        // return all of the attributes as both data and attributes since we have
        // now way to partition them. The user can exclude attributes manually.
        if (! class_exists($class)) {
            return [collect($attributes), collect($attributes)];
        }

        $constructor = (new ReflectionClass($class))->getConstructor();

        $parameterNames = $constructor
            ? collect($constructor->getParameters())->map->getName()->all()
            : [];

        return collect($attributes)->partition(function ($value, $key) use ($parameterNames) {
            return in_array(Str::camel($key), $parameterNames);
        })->all();
    }

    protected function attributesToString(array $attributes, $escapeBound = true)
    {
        return collect($attributes)
            ->map(function (string $value, string $attribute) use ($escapeBound) {
                return $escapeBound && isset($this->boundAttributes[$attribute]) && $value !== 'true' && ! is_numeric($value)
                    ? "'{$attribute}' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute({$value})"
                    : "'{$attribute}' => {$value}";
            })
            ->implode(',');
    }
}
