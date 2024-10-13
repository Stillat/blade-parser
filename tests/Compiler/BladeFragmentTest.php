<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('fragment starts are compiled', function () {
    expect($this->compiler->compileString('@fragment(\'foo\')'))->toBe('<?php $__env->startFragment(\'foo\'); ?>');
    expect($this->compiler->compileString('@fragment(name(foo))'))->toBe('<?php $__env->startFragment(name(foo)); ?>');
});

test('end fragments are compiled', function () {
    expect($this->compiler->compileString('@endfragment'))->toBe('<?php echo $__env->stopFragment(); ?>');
});
