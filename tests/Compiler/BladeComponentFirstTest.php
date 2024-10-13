<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('component firsts are compiled', function () {
    expect($this->compiler->compileString('@componentFirst(["one", "two"])'))->toBe('<?php $__env->startComponentFirst(["one", "two"]); ?>');
    expect($this->compiler->compileString('@componentFirst(["one", "two"], ["foo" => "bar"])'))->toBe('<?php $__env->startComponentFirst(["one", "two"], ["foo" => "bar"]); ?>');
});
