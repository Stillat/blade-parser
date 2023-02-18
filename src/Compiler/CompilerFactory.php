<?php

namespace Stillat\BladeParser\Compiler;

class CompilerFactory
{
    /**
     * A factory method that can be used to construct Compiler instances.
     *
     * @var callable|null
     */
    public static $factory = null;

    public static function makeCompiler(): Compiler
    {
        $methodToCall = self::$factory;

        if (is_callable($methodToCall)) {
            return $methodToCall();
        }

        return app(Compiler::class);
    }
}
