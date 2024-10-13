<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('statement that contains non consecutive parenthesis are compiled', function () {
    $string = "Foo @lang(function_call('foo(blah)')) bar";
    $expected = "Foo <?php echo app('translator')->get(function_call('foo(blah)')); ?> bar";
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('language and choices are compiled', function () {
    expect($this->compiler->compileString("@lang('foo')"))->toBe('<?php echo app(\'translator\')->get(\'foo\'); ?>');
    expect($this->compiler->compileString("@choice('foo', 1)"))->toBe('<?php echo app(\'translator\')->choice(\'foo\', 1); ?>');
});

test('language no parameters are compiled', function () {
    expect($this->compiler->compileString('@lang'))->toBe('<?php $__env->startTranslation(); ?>');
});

test('lang start translations are compiled', function () {
    $expected = <<<'EXPECTED'
<?php $__env->startTranslation(["thing"]); ?>
EXPECTED;

    expect($this->compiler->compileString('@lang (["thing"])'))->toBe($expected);
});
