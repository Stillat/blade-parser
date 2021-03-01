<?php

namespace Stillat\BladeParser\Tests;

use InvalidArgumentException;

class BladeCustomTest extends ParserTestCase
{
    public function testCustomPhpCodeIsCorrectlyHandled()
    {
        $this->assertSame('<?php if($test): ?> <?php @show(\'test\'); ?> <?php endif; ?>', $this->compiler->compileString("@if(\$test) <?php @show('test'); ?> @endif"));
    }

    public function testMixingYieldAndEcho()
    {
        $this->assertSame('<?php echo $__env->yieldContent(\'title\'); ?> - <?php echo e(Config::get(\'site.title\')); ?>', $this->compiler->compileString("@yield('title') - {{Config::get('site.title')}}"));
    }

    public function testCustomStatements()
    {
        $this->assertCount(0, $this->compiler->getCustomDirectives());
        $this->compiler->directive('customControl', function ($expression) {
            return "<?php echo custom_control({$expression}); ?>";
        });
        $this->assertCount(1, $this->compiler->getCustomDirectives());

        $string = '@if($foo)
@customControl(10, $foo, \'bar\')
@endif';
        $expected = '<?php if($foo): ?>
<?php echo custom_control(10, $foo, \'bar\'); ?>
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testCustomShortStatements()
    {
        $this->compiler->directive('customControl', function ($expression) {
            return '<?php echo custom_control(); ?>';
        });

        $string = '@customControl';
        $expected = '<?php echo custom_control(); ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testCustomExtensionsAreCompiled()
    {
        $this->compiler->extend(function ($value) {
            return str_replace('foo', 'bar', $value);
        });
        $this->assertSame('bar', $this->compiler->compileString('foo'));
    }

    public function testValidCustomNames()
    {
        $this->assertNull($this->compiler->directive('custom', function () {
            //
        }));
        $this->assertNull($this->compiler->directive('custom_custom', function () {
            //
        }));
        $this->assertNull($this->compiler->directive('customCustom', function () {
            //
        }));
        $this->assertNull($this->compiler->directive('custom::custom', function () {
            //
        }));
    }

    public function testInvalidCustomNames()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The directive name [custom-custom] is not valid.');
        $this->compiler->directive('custom-custom', function () {
            //
        });
    }

    public function testInvalidCustomNames2()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The directive name [custom:custom] is not valid.');
        $this->compiler->directive('custom:custom', function () {
            //
        });
    }

    public function testCustomExtensionOverwritesCore()
    {
        $this->compiler->directive('foreach', function ($expression) {
            return '<?php custom(); ?>';
        });

        $string = '@foreach';
        $expected = '<?php custom(); ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

}