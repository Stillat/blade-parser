<?php

namespace Stillat\BladeParser\Tests\Compiler;

use Stillat\BladeParser\Compiler\CompilerServices\CoreDirectiveRetriever;
use Stillat\BladeParser\Parser\CoreDirectives;
use Stillat\BladeParser\Tests\ParserTestCase;

class CoreDirectivesWithoutArgumentsTest extends ParserTestCase
{
    /**
     * @dataProvider coreDirectives
     */
    public function testCoreDirectivesCanBeCompiledWithoutArgumentsAndNotThrowNullReferenceErrors($directive)
    {
        $this->expectNotToPerformAssertions();
        $this->compiler->compileString($directive);
    }

    public function coreDirectives(): array
    {
        return collect(array_diff(CoreDirectiveRetriever::instance()->getDirectiveNames(), ['foreach', 'forelse', 'endverbatim', 'use']))->map(function ($name) {
            return ['@'.$name];
        })->all();
    }
}
