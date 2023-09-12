<?php

namespace Stillat\BladeParser\Document;

class DocumentOptions
{
    public function __construct(
        /**
         * Determines if core Laravel Blade directives should be parsed.
         */
        public bool $withCoreDirectives = true,
        /**
         * A list of custom directive names to parse.
         *
         * @var string[] $customDirectives
         */
        public array $customDirectives = [],
    ) {
    }
}
