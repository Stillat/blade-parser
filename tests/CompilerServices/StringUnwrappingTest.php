<?php

namespace Stillat\BladeParser\Tests\CompilerServices;

use Stillat\BladeParser\Compiler\CompilerServices\StringUtilities;
use Stillat\BladeParser\Tests\ParserTestCase;

class StringUnwrappingTest extends ParserTestCase
{
    /**
     * @dataProvider stringUnwrapDataProvider
     */
    public function testBasicStringUnwrapping($input, $expected)
    {
        $this->assertSame($expected, StringUtilities::unwrapParentheses($input));
    }

    public static function stringUnwrapDataProvider()
    {
        return [
            ['((()))', ''],
            ['(', '('],
            [')', ')'],
            ['(()', '('],
            ['((((Foo)))', '(Foo'],
            ['Foo)))))', 'Foo)))))'],
            ['(((((((Foo)))))))', 'Foo'],
        ];
    }
}
