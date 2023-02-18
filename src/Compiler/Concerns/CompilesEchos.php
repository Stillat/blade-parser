<?php

namespace Stillat\BladeParser\Compiler\Concerns;

use Closure;
use Illuminate\Support\Str;
use ReflectionException;
use Stillat\BladeParser\Compiler\CompilationTarget;
use Stillat\BladeParser\Nodes\EchoNode;

trait CompilesEchos
{
    /**
     * The list of echo handlers.
     */
    protected array $echoHandlers = [];

    /**
     * Sets and overrides all existing echo handlers.
     *
     * In default setups, this is set to the value of the
     * `Illuminate\View\Compilers\BladeCompiler::$echoHandlers`
     * protected property.
     *
     * @param  array  $handlers The echo handlers.
     */
    public function setEchoHandlers(array $handlers): void
    {
        $this->echoHandlers = $handlers;
    }

    /**
     * Returns all configured echo handlers.
     */
    public function getEchoHandlers(): array
    {
        return $this->echoHandlers;
    }

    /**
     * @throws ReflectionException
     */
    public function stringable($class, $handler = null)
    {
        if ($class instanceof Closure) {
            [$class, $handler] = [$this->firstClosureParameterType($class), $class];
        }

        $this->echoHandlers[$class] = $handler;
    }

    protected function compileRawEchoNode(EchoNode $node): string
    {
        if ($this->compilationTarget == CompilationTarget::ComponentParameter) {
            return '.'.trim($node->innerContent).'.';
        }

        return '<?php echo '.$this->wrapInEchoHandler(trim($node->innerContent)).'; ?>';
    }

    protected function compileEchoNode(EchoNode $node): string
    {
        $wrapped = $this->wrapInEchoHandler(trim($node->innerContent));
        $formatted = sprintf($this->echoFormat, $wrapped);

        if ($this->compilationTarget == CompilationTarget::ComponentParameter) {
            return '\'.'.$formatted.'.\'';
        }

        return '<?php echo '.$formatted.'; ?>';
    }

    protected function wrapInEchoHandler(string $value): string
    {
        $value = Str::of($value)
            ->trim()
            ->when(str_ends_with($value, ';'), function ($str) {
                return $str->beforeLast(';');
            });

        return empty($this->echoHandlers) ? $value : '$__bladeCompiler->applyEchoHandler('.$value.')';
    }

    /**
     * Add an instance of the blade echo handler to the start of the compiled string.
     */
    protected function addBladeCompilerVariable(string $result): string
    {
        return "<?php \$__bladeCompiler = app('blade.compiler'); ?>".$result;
    }

    /**
     * Apply the echo handler for the value if it exists.
     *
     * @param  string|mixed  $value
     */
    public function applyEchoHandler(mixed $value): string
    {
        if (is_object($value) && isset($this->echoHandlers[get_class($value)])) {
            return call_user_func($this->echoHandlers[get_class($value)], $value);
        }

        return $value;
    }
}
