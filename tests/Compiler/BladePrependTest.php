<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Illuminate\Support\Str;

test('prepend is compiled', function () {
    $string = '@prepend(\'foo\')
bar
@endprepend';
    $expected = '<?php $__env->startPrepend(\'foo\'); ?>
bar
<?php $__env->stopPrepend(); ?>';

    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('prepend once is compiled', function () {
    $string = '@prependOnce(\'foo\', \'bar\')
test
@endPrependOnce';

    $expected = '<?php if (! $__env->hasRenderedOnce(\'bar\')): $__env->markAsRenderedOnce(\'bar\');
$__env->startPrepend(\'foo\'); ?>
test
<?php $__env->stopPrepend(); endif; ?>';

    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('prepend once is compiled when id is missing', function () {
    Str::createUuidsUsing(fn () => 'e60e8f77-9ac3-4f71-9f8e-a044ef481d7f');

    $string = '@prependOnce(\'foo\')
test
@endPrependOnce';

    $expected = '<?php if (! $__env->hasRenderedOnce(\'e60e8f77-9ac3-4f71-9f8e-a044ef481d7f\')): $__env->markAsRenderedOnce(\'e60e8f77-9ac3-4f71-9f8e-a044ef481d7f\');
$__env->startPrepend(\'foo\'); ?>
test
<?php $__env->stopPrepend(); endif; ?>';

    expect($this->compiler->compileString($string))->toEqual($expected);
});
