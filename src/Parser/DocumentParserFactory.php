<?php

namespace Stillat\BladeParser\Parser;

class DocumentParserFactory
{
    /**
     * A factory method that can be used to construct DocumentParser instances.
     *
     * @var callable|null
     */
    public static $factory = null;

    public static function makeDocumentParser(): DocumentParser
    {
        $methodToCall = self::$factory;

        if (is_callable($methodToCall)) {
            return $methodToCall();
        }

        return app(DocumentParser::class);
    }
}
