<?php

namespace Stillat\BladeParser\Tests\Compiler;

use Stillat\BladeParser\Tests\ParserTestCase;

class BladeLangTest extends ParserTestCase
{
    public function testStatementThatContainsNonConsecutiveParenthesisAreCompiled()
    {
        $string = "Foo @lang(function_call('foo(blah)')) bar";
        $expected = "Foo <?php echo app('translator')->get(function_call('foo(blah)')); ?> bar";
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testLanguageAndChoicesAreCompiled()
    {
        $this->assertSame('<?php echo app(\'translator\')->get(\'foo\'); ?>', $this->compiler->compileString("@lang('foo')"));
        $this->assertSame('<?php echo app(\'translator\')->choice(\'foo\', 1); ?>', $this->compiler->compileString("@choice('foo', 1)"));
    }

    public function testLanguageNoParametersAreCompiled()
    {
        $this->assertSame('<?php $__env->startTranslation(); ?>', $this->compiler->compileString('@lang'));
    }

    public function testLangStartTranslationsAreCompiled()
    {
        $expected = <<<'EXPECTED'
<?php $__env->startTranslation(["thing"]); ?>
EXPECTED;

        $this->assertSame($expected, $this->compiler->compileString('@lang (["thing"])'));
    }
}
