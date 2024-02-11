<?php

namespace Stillat\BladeParser\Compiler\Concerns;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Stillat\BladeParser\Compiler\CompilerServices\DirectiveNameValidator;

trait ManagesCustomDirectives
{
    /**
     * Sets the compiler's custom directives compilers.
     *
     * @param  array  $directives  The directive compilers.
     */
    public function setCustomDirectives(array $directives): void
    {
        $this->customDirectives = $directives;
    }

    /**
     * Get the list of custom directives.
     */
    public function getCustomDirectives(): array
    {
        return $this->customDirectives;
    }

    /**
     * Register a component alias directive.
     *
     * This method has the same behavior as the
     * `Illuminate\View\Compilers\BladeCompiler::aliasComponent` method. You do
     * *not* need to manually call this method to sync compiler information
     * if you use the default compiler factory methods/service bindings.
     */
    public function aliasComponent(string $path, ?string $alias = null): void
    {
        $alias = $alias ?: Arr::last(explode('.', $path));

        $this->directive($alias, function ($expression) use ($path) {
            return $expression
                ? "<?php \$__env->startComponent('{$path}', {$expression}); ?>"
                : "<?php \$__env->startComponent('{$path}'); ?>";
        });

        $this->directive('end'.$alias, function ($expression) {
            return '<?php echo $__env->renderComponent(); ?>';
        });
    }

    /**
     * Register an include alias directive.
     *
     * This method has the same behavior as the
     * `Illuminate\View\Compilers\BladeCompiler::include` method. You do
     * *not* need to manually call this method to sync compiler information
     * if you use the default compiler factory methods/service bindings.
     */
    public function include(string $path, ?string $alias = null): void
    {
        $this->aliasInclude($path, $alias);
    }

    /**
     * Register an include alias directive.
     *
     * This method has the same behavior as the
     * `Illuminate\View\Compilers\BladeCompiler::aliasInclude` method. You do
     * *not* need to manually call this method to sync compiler information
     * if you use the default compiler factory methods/service bindings.
     */
    public function aliasInclude(string $path, ?string $alias = null): void
    {
        $alias = $alias ?: Arr::last(explode('.', $path));

        $this->directive($alias, function ($expression) use ($path) {
            $expression = $this->stripParentheses($expression) ?: '[]';

            return "<?php echo \$__env->make('{$path}', {$expression}, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>";
        });
    }

    /**
     * Registers a handler for custom directives.
     *
     * This method has the same behavior as the
     * `Illuminate\View\Compilers\BladeCompiler::directive` method. You do
     * *not* need to manually call this method to sync compiler information
     * if you use the default compiler factory methods/service bindings.
     *
     * @param  string  $name  The directive name.
     * @param  callable  $handler  The handler
     *
     * @throws InvalidArgumentException
     */
    public function directive(string $name, callable $handler): void
    {
        if (! DirectiveNameValidator::isNameValid($name)) {
            throw new InvalidArgumentException('The directive name ['.$name.'] is not valid. Directive names must only contain alphanumeric characters and underscores.');
        }

        $this->customDirectives[$name] = $handler;
        $this->parser->registerCustomDirective($name);
    }
}
