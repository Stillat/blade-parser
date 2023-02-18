<?php

namespace Stillat\BladeParser\Tests\Compiler;

use Illuminate\Support\Str;
use Stillat\BladeParser\Tests\ParserTestCase;

class BladePushTest extends ParserTestCase
{
    public function testPushIsCompiled()
    {
        $string = '@push(\'foo\')
test
@endpush';
        $expected = '<?php $__env->startPush(\'foo\'); ?>
test
<?php $__env->stopPush(); ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testPushOnceIsCompiled()
    {
        $string = '@pushOnce(\'foo\', \'bar\')
test
@endPushOnce';

        $expected = '<?php if (! $__env->hasRenderedOnce(\'bar\')): $__env->markAsRenderedOnce(\'bar\');
$__env->startPush(\'foo\'); ?>
test
<?php $__env->stopPush(); endif; ?>';

        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testPushOnceIsCompiledWhenIdIsMissing()
    {
        Str::createUuidsUsing(fn () => 'e60e8f77-9ac3-4f71-9f8e-a044ef481d7f');

        $string = '@pushOnce(\'foo\')
test
@endPushOnce';

        $expected = '<?php if (! $__env->hasRenderedOnce(\'e60e8f77-9ac3-4f71-9f8e-a044ef481d7f\')): $__env->markAsRenderedOnce(\'e60e8f77-9ac3-4f71-9f8e-a044ef481d7f\');
$__env->startPush(\'foo\'); ?>
test
<?php $__env->stopPush(); endif; ?>';

        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testPushIfIsCompiled()
    {
        $string = <<<'EOT'
@pushIf($something, 'test')
EOT;
        $expected = <<<'EXPECTED'
<?php if(($something): $__env->startPush( 'test')); ?>
EXPECTED;

        $this->assertSame($expected, $this->compiler->compileString($string));
    }
}
