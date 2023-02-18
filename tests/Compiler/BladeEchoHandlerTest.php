<?php

namespace Stillat\BladeParser\Tests\Compiler;

use Exception;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use Stillat\BladeParser\Tests\ParserTestCase;

class BladeEchoHandlerTest extends ParserTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->compiler->stringable(function (Fluent $object) {
            return 'Hello World';
        });
    }

    public function testBladeHandlerCanInterceptRegularEchos()
    {
        $this->assertSame(
            "<?php \$__bladeCompiler = app('blade.compiler'); ?><?php echo e(\$__bladeCompiler->applyEchoHandler(\$exampleObject)); ?>",
            $this->compiler->compileString('{{$exampleObject}}')
        );
    }

    public function testBladeHandlerCanInterceptRawEchos()
    {
        $this->assertSame(
            "<?php \$__bladeCompiler = app('blade.compiler'); ?><?php echo \$__bladeCompiler->applyEchoHandler(\$exampleObject); ?>",
            $this->compiler->compileString('{!!$exampleObject!!}')
        );
    }

    public function testBladeHandlerCanInterceptEscapedEchos()
    {
        $this->assertSame(
            "<?php \$__bladeCompiler = app('blade.compiler'); ?><?php echo e(\$__bladeCompiler->applyEchoHandler(\$exampleObject)); ?>",
            $this->compiler->compileString('{{{$exampleObject}}}')
        );
    }

    public function testWhitespaceIsPreservedCorrectly()
    {
        $this->assertSame(
            "<?php \$__bladeCompiler = app('blade.compiler'); ?><?php echo e(\$__bladeCompiler->applyEchoHandler(\$exampleObject)); ?>\n",
            $this->compiler->compileString("{{\$exampleObject}}\n")
        );
    }

    /**
     * @dataProvider handlerLogicDataProvider
     */
    public function testHandlerLogicWorksCorrectly($blade)
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The fluent object has been successfully handled!');

        Blade::stringable(Fluent::class, function ($object) {
            throw new Exception('The fluent object has been successfully handled!');
        });
        $exampleObject = new Fluent();

        eval(Str::of($this->compiler->compileString($blade))->remove(['<?php', '?>']));
    }

    public static function handlerLogicDataProvider()
    {
        return [
            ['{{$exampleObject}}'],
            ['{{$exampleObject;}}'],
            ['{{{$exampleObject;}}}'],
            ['{!!$exampleObject;!!}'],
        ];
    }

    /**
     * @dataProvider nonStringableDataProvider
     */
    public function testHandlerWorksWithNonStringables($blade, $expectedOutput)
    {
        ob_start();
        eval(Str::of($this->compiler->compileString($blade))->remove(['<?php', '?>']));
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertSame($expectedOutput, $output);
    }

    public static function nonStringableDataProvider()
    {
        return [
            ['{{"foo" . "bar"}}', 'foobar'],
            ['{{ 1 + 2 }}{{ "test"; }}', '3test'],
            ['@php($test = "hi"){{ $test }}', 'hi'],
            ['{!! "&nbsp;" !!}', '&nbsp;'],
        ];
    }
}
