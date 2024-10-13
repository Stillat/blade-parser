<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('expressions on the same line', function () {
    expect($this->compiler->compileString('@lang(foo(bar(baz(qux(breeze()))))) space () @lang(foo(bar))'))->toBe('<?php echo app(\'translator\')->get(foo(bar(baz(qux(breeze()))))); ?> space () <?php echo app(\'translator\')->get(foo(bar)); ?>');
});

test('expression within html', function () {
    expect($this->compiler->compileString('<html {{ $foo }}>'))->toBe('<html <?php echo e($foo); ?>>');
    expect($this->compiler->compileString('<html{{ $foo }}>'))->toBe('<html<?php echo e($foo); ?>>');
    expect($this->compiler->compileString('<html {{ $foo }} @lang(\'foo\')>'))->toBe('<html <?php echo e($foo); ?> <?php echo app(\'translator\')->get(\'foo\'); ?>>');
});
