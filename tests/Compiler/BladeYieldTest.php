<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('yields are compiled', function () {
    expect($this->compiler->compileString('@yield(\'foo\')'))->toBe('<?php echo $__env->yieldContent(\'foo\'); ?>');
    expect($this->compiler->compileString('@yield(\'foo\', \'bar\')'))->toBe('<?php echo $__env->yieldContent(\'foo\', \'bar\'); ?>');
    expect($this->compiler->compileString('@yield(name(foo))'))->toBe('<?php echo $__env->yieldContent(name(foo)); ?>');
});
