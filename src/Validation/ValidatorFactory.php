<?php

namespace Stillat\BladeParser\Validation;

class ValidatorFactory
{
    /**
     * A factory method that can be used to construct BladeValidator instances.
     *
     * @var callable|null
     */
    public static $factory = null;

    /**
     * Constructs a new instance of `BladeValidator`.
     */
    public static function makeBladeValidator(): BladeValidator
    {
        $methodToCall = self::$factory;

        if (is_callable($methodToCall)) {
            return $methodToCall();
        }

        return app(BladeValidator::class);
    }
}
