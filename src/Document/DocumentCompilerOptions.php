<?php

namespace Stillat\BladeParser\Document;

class DocumentCompilerOptions
{
    /**
     * @param  bool  $failOnParserErrors  Indicates if the compiler should fail on parser errors.
     * @param  bool  $failStrictly  Indicates if the compiler should fail on any error.
     * @param  bool  $throwExceptionOnUnknownComponentClass  Indicates if the compiler should throw exceptions when encountering unknown component classes.
     * @param  callable[]  $appendCallbacks  A list of append callbacks that the compiler will invoke.
     * @param  array  $customTagCompilers  A mapping of custom tag compilers.
     * @param  bool  $compileCoreComponentTags  Whether to compile core component tags.
     */
    public function __construct(
        public bool $failOnParserErrors = false,
        public bool $failStrictly = false,
        public bool $throwExceptionOnUnknownComponentClass = true,
        public array $appendCallbacks = [],
        public array $customTagCompilers = [],
        public bool $compileCoreComponentTags = true,
        public array $ignoreDirectives = []) {}
}
