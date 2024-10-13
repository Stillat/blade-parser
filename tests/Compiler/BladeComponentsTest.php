<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Illuminate\View\Component;
use Illuminate\View\ComponentAttributeBag;
use Mockery as m;

test('components are compiled', function () {
    expect($this->compiler->compileString('@component(\'foo\', ["foo" => "bar"])'))->toBe('<?php $__env->startComponent(\'foo\', ["foo" => "bar"]); ?>');
    expect($this->compiler->compileString('@component(\'foo\')'))->toBe('<?php $__env->startComponent(\'foo\'); ?>');
});

test('class components are compiled', function () {
    expect($this->compiler->compileString('@component(\'Stillat\BladeParser\Tests\Compiler\ComponentStub::class\', \'test\', ["foo" => "bar"])'))->toBe('<?php if (isset($component)) { $__componentOriginald797ca481a3632d6131474d33f4e32d7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald797ca481a3632d6131474d33f4e32d7 = $attributes; } ?>
<?php $component = Stillat\BladeParser\Tests\Compiler\ComponentStub::class::resolve(["foo" => "bar"] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName(\'test\'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>');
});

test('end components are compiled', function () {
    $this->compiler->newComponentHash('foo');

    expect($this->compiler->compileString('@endcomponent'))->toBe('<?php echo $__env->renderComponent(); ?>');
});

test('end component classes are compiled', function () {
    $this->compiler->newComponentHash('foo');

    expect($this->compiler->compileString('@endcomponentClass'))->toBe('<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal79aef92e83454121ab6e5f64077e7d8a)): ?>
<?php $attributes = $__attributesOriginal79aef92e83454121ab6e5f64077e7d8a; ?>
<?php unset($__attributesOriginal79aef92e83454121ab6e5f64077e7d8a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal79aef92e83454121ab6e5f64077e7d8a)): ?>
<?php $component = $__componentOriginal79aef92e83454121ab6e5f64077e7d8a; ?>
<?php unset($__componentOriginal79aef92e83454121ab6e5f64077e7d8a); ?>
<?php endif; ?>');
});

test('slots are compiled', function () {
    expect($this->compiler->compileString('@slot(\'foo\', null, ["foo" => "bar"])'))->toBe('<?php $__env->slot(\'foo\', null, ["foo" => "bar"]); ?>');
    expect($this->compiler->compileString('@slot(\'foo\')'))->toBe('<?php $__env->slot(\'foo\'); ?>');
});

test('end slots are compiled', function () {
    expect($this->compiler->compileString('@endslot'))->toBe('<?php $__env->endSlot(); ?>');
});

test('props are extracted from parent attributes correctly for class components', function () {
    $attributes = new ComponentAttributeBag(['foo' => 'baz', 'other' => 'ok']);

    $component = m::mock(Component::class);
    $component->shouldReceive('withName', 'test');
    $component->shouldReceive('shouldRender')->andReturn(false);

    Component::resolveComponentsUsing(fn () => $component);

    $template = $this->compiler->compileString('@component(\'Stillat\BladeParser\Tests\Compiler\ComponentStub::class\', \'test\', ["foo" => "bar"])');

    ob_start();
    eval(" ?> $template <?php endif; ");
    ob_get_clean();
});
