<?php

namespace Stillat\BladeParser\Compiler\CompilerServices;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Nodes\Components\ParameterNode;
use Stillat\BladeParser\Nodes\Components\ParameterType;

class AttributeCompiler
{
    protected array $attributeWrapCallbacks = [];

    protected string $escapedParameterPrefix = '';

    public function prefixEscapedParametersWith(string $prefix): static
    {
        $this->escapedParameterPrefix = $prefix;

        return $this;
    }

    public function wrapResultIn(string|array $attribute, callable $callback): static
    {
        if (! is_array($attribute)) {
            $attribute = [$attribute];
        }

        foreach ($attribute as $attributeName) {
            $this->attributeWrapCallbacks[$attributeName] = $callback;
        }

        return $this;
    }

    protected function applyWraps(string $attribute, string $value): string
    {
        if (! array_key_exists($attribute, $this->attributeWrapCallbacks)) {
            return $value;
        }

        return call_user_func($this->attributeWrapCallbacks[$attribute], $value);
    }

    protected function getParamValue(string $value): string
    {
        return Str::replace("'", "\\'", $value);
    }

    protected function toArraySyntax(string $name, string $value, bool $isString = true): string
    {
        if ($isString) {
            $value = "'{$value}'";
        }

        $value = $this->applyWraps($name, $value);

        return "'{$name}'=>{$value}";
    }

    protected function compileAttributeEchos(string $attributeString): string
    {
        $value = Blade::compileEchos($attributeString);

        $value = $this->escapeSingleQuotesOutsideOfPhpBlocks($value);

        $value = str_replace('<?php echo ', '\'.', $value);

        return str_replace('; ?>', '.\'', $value);
    }

    protected function escapeSingleQuotesOutsideOfPhpBlocks(string $value): string
    {
        return collect(token_get_all($value))->map(function ($token) {
            if (! is_array($token)) {
                return $token;
            }

            return $token[0] === T_INLINE_HTML
                ? str_replace("'", "\\'", $token[1])
                : $token[1];
        })->implode('');
    }

    public function compileComponent(ComponentNode $component): string
    {
        return $this->compile($component->parameters);
    }

    public function compile(array $parameters): string
    {
        return '['.implode(',', $this->toCompiledArray($parameters)).']';
    }

    /**
     * @param  ParameterNode[]  $parameters
     */
    public function toCompiledArray(array $parameters): array
    {
        if (count($parameters) === 0) {
            return [];
        }

        $compiledParameters = [];

        foreach ($parameters as $parameter) {
            if ($parameter->type == ParameterType::Parameter) {
                $compiledParameters[] = $this->toArraySyntax($parameter->name, $this->getParamValue($parameter->value));
            } elseif ($parameter->type == ParameterType::DynamicVariable) {
                $compiledParameters[] = $this->toArraySyntax($parameter->materializedName, $parameter->value, false);
            } elseif ($parameter->type == ParameterType::ShorthandDynamicVariable) {
                $compiledParameters[] = $this->toArraySyntax($parameter->materializedName, $parameter->value, false);
            } elseif ($parameter->type == ParameterType::EscapedParameter) {
                $compiledParameters[] = $this->toArraySyntax($this->escapedParameterPrefix.$parameter->materializedName, $parameter->value);
            } elseif ($parameter->type == ParameterType::Attribute) {
                $compiledParameters[] = $this->toArraySyntax($parameter->materializedName, 'true', false);
            } elseif ($parameter->type == ParameterType::InterpolatedValue) {
                $compiledParameters[] = $this->toArraySyntax($parameter->materializedName, "'".$this->compileAttributeEchos($parameter->value)."'", false);
            }
        }

        return $compiledParameters;
    }
}
