<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Illuminate\Support\Str;

test('push is compiled', function () {
    $string = '@push(\'foo\')
test
@endpush';
    $expected = '<?php $__env->startPush(\'foo\'); ?>
test
<?php $__env->stopPush(); ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('push once is compiled', function () {
    $string = '@pushOnce(\'foo\', \'bar\')
test
@endPushOnce';

    $expected = '<?php if (! $__env->hasRenderedOnce(\'bar\')): $__env->markAsRenderedOnce(\'bar\');
$__env->startPush(\'foo\'); ?>
test
<?php $__env->stopPush(); endif; ?>';

    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('push once is compiled when id is missing', function () {
    Str::createUuidsUsing(fn () => 'e60e8f77-9ac3-4f71-9f8e-a044ef481d7f');

    $string = '@pushOnce(\'foo\')
test
@endPushOnce';

    $expected = '<?php if (! $__env->hasRenderedOnce(\'e60e8f77-9ac3-4f71-9f8e-a044ef481d7f\')): $__env->markAsRenderedOnce(\'e60e8f77-9ac3-4f71-9f8e-a044ef481d7f\');
$__env->startPush(\'foo\'); ?>
test
<?php $__env->stopPush(); endif; ?>';

    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('push if is compiled', function () {
    $string = <<<'EOT'
@pushIf($something, 'test')
EOT;
    $expected = <<<'EXPECTED'
<?php if(($something): $__env->startPush( 'test')); ?>
EXPECTED;

    expect($this->compiler->compileString($string))->toBe($expected);
});
