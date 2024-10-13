<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('section starts are compiled', function () {
    expect($this->compiler->compileString('@section(\'foo\')'))->toBe('<?php $__env->startSection(\'foo\'); ?>');
    expect($this->compiler->compileString('@section(name(foo))'))->toBe('<?php $__env->startSection(name(foo)); ?>');
});
