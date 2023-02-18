<?php

namespace Stillat\BladeParser\Document;

class DocumentFactory
{
    /**
     * A factory method that can be used to construct Document instances.
     *
     * @var callable|null
     */
    public static $factory = null;

    public static function makeDocument(): Document
    {
        $methodToCall = self::$factory;

        if (is_callable($methodToCall)) {
            return $methodToCall();
        }

        return new Document();
    }
}
