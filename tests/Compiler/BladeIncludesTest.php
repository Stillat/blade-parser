<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('eachs are compiled', function () {
    expect($this->compiler->compileString('@each(\'foo\', \'bar\')'))->toBe('<?php echo $__env->renderEach(\'foo\', \'bar\'); ?>');
    expect($this->compiler->compileString('@each(name(foo))'))->toBe('<?php echo $__env->renderEach(name(foo)); ?>');
});

test('includes are compiled', function () {
    expect($this->compiler->compileString('@include(\'foo\')'))->toBe('<?php echo $__env->make(\'foo\', \Illuminate\Support\Arr::except(get_defined_vars(), [\'__data\', \'__path\']))->render(); ?>');
    expect($this->compiler->compileString('@include(name(foo))'))->toBe('<?php echo $__env->make(name(foo), \Illuminate\Support\Arr::except(get_defined_vars(), [\'__data\', \'__path\']))->render(); ?>');
});

test('include ifs are compiled', function () {
    expect($this->compiler->compileString('@includeIf(\'foo\')'))->toBe('<?php if ($__env->exists(\'foo\')) echo $__env->make(\'foo\', \Illuminate\Support\Arr::except(get_defined_vars(), [\'__data\', \'__path\']))->render(); ?>');
    expect($this->compiler->compileString('@includeIf(name(foo))'))->toBe('<?php if ($__env->exists(name(foo))) echo $__env->make(name(foo), \Illuminate\Support\Arr::except(get_defined_vars(), [\'__data\', \'__path\']))->render(); ?>');
});

test('include whens are compiled', function () {
    expect($this->compiler->compileString('@includeWhen(true, \'foo\', ["foo" => "bar"])'))->toBe('<?php echo $__env->renderWhen(true, \'foo\', ["foo" => "bar"], \Illuminate\Support\Arr::except(get_defined_vars(), [\'__data\', \'__path\'])); ?>');
    expect($this->compiler->compileString('@includeWhen(true, \'foo\')'))->toBe('<?php echo $__env->renderWhen(true, \'foo\', \Illuminate\Support\Arr::except(get_defined_vars(), [\'__data\', \'__path\'])); ?>');
});

test('include unlesses are compiled', function () {
    expect($this->compiler->compileString('@includeUnless(true, \'foo\', ["foo" => "bar"])'))->toBe('<?php echo $__env->renderUnless(true, \'foo\', ["foo" => "bar"], \Illuminate\Support\Arr::except(get_defined_vars(), [\'__data\', \'__path\'])); ?>');
    expect($this->compiler->compileString('@includeUnless($undefined ?? true, \'foo\')'))->toBe('<?php echo $__env->renderUnless($undefined ?? true, \'foo\', \Illuminate\Support\Arr::except(get_defined_vars(), [\'__data\', \'__path\'])); ?>');
});

test('include firsts are compiled', function () {
    expect($this->compiler->compileString('@includeFirst(["one", "two"])'))->toBe('<?php echo $__env->first(["one", "two"], \Illuminate\Support\Arr::except(get_defined_vars(), [\'__data\', \'__path\']))->render(); ?>');
    expect($this->compiler->compileString('@includeFirst(["one", "two"], ["foo" => "bar"])'))->toBe('<?php echo $__env->first(["one", "two"], ["foo" => "bar"], \Illuminate\Support\Arr::except(get_defined_vars(), [\'__data\', \'__path\']))->render(); ?>');
});
