<?php

namespace Stillat\BladeParser\Compiler\Concerns;

trait ManagesCustomConditions
{
    /**
     * Register an "if" statement directive.
     *
     * This method has the same behavior as the
     * `Illuminate\View\Compilers\BladeCompiler::if` method. You do
     * *not* need to manually call this method to sync compiler information
     * if you use the default compiler factory methods/service bindings.
     *
     * @param  string  $name The condition handler name.
     * @param  callable  $callback The condition handler.
     */
    public function if(string $name, callable $callback): void
    {
        $this->conditions[$name] = $callback;

        $this->directive($name, function ($expression) use ($name) {
            return $expression !== ''
                ? "<?php if (\Illuminate\Support\Facades\Blade::check('{$name}', {$expression})): ?>"
                : "<?php if (\Illuminate\Support\Facades\Blade::check('{$name}')): ?>";
        });

        $this->directive('unless'.$name, function ($expression) use ($name) {
            return $expression !== ''
                ? "<?php if (! \Illuminate\Support\Facades\Blade::check('{$name}', {$expression})): ?>"
                : "<?php if (! \Illuminate\Support\Facades\Blade::check('{$name}')): ?>";
        });

        $this->directive('else'.$name, function ($expression) use ($name) {
            return $expression !== ''
                ? "<?php elseif (\Illuminate\Support\Facades\Blade::check('{$name}', {$expression})): ?>"
                : "<?php elseif (\Illuminate\Support\Facades\Blade::check('{$name}')): ?>";
        });

        $this->directive('end'.$name, function () {
            return '<?php endif; ?>';
        });
    }

    /**
     * Check the result of a condition.
     *
     * @param  array  $parameters
     */
    public function check(string $name, ...$parameters): bool
    {
        return call_user_func($this->conditions[$name], ...$parameters);
    }
}
