<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);

use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\Component;
use Illuminate\View\ComponentAttributeBag;
use Mockery as m;
use Stillat\BladeParser\Compiler\CompilerServices\StringUtilities;
use Stillat\BladeParser\Contracts\CustomComponentTagCompiler;
use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Tests\Compiler\TestAlertComponent;
use Stillat\BladeParser\Tests\Compiler\TestProfileComponent;

afterEach(function () {
    m::close();
});

test('slots can be compiled', function () {
    $template = <<<'EOT'
<x-slot name="foo">
</x-slot>
EOT;

    $expected = <<<'EXPECTED'
 @slot('foo', null, []) 
 @endslot
EXPECTED;

    $result = compiler()->compileTags($template);

    expect($result)->toBe($expected);
});

test('inline slots can be compiled', function () {
    $template = <<<'EOT'
<x-slot:foo>
</x-slot>
EOT;

    $expected = <<<'EXPECTED'
 @slot('foo', null, []) 
 @endslot
EXPECTED;

    $result = compiler()->compileTags($template);

    expect($result)->toBe($expected);
});

test('dynamic slots can be compiled', function () {
    $template = <<<'EOT'
<x-slot :name="$foo">
</x-slot>
EOT;

    $expected = <<<'EXPECTED'
 @slot($foo, null, []) 
 @endslot
EXPECTED;

    $result = compiler()->compileTags($template);

    expect($result)->toBe($expected);
});

test('slots with attributes can be compiled', function () {
    $template = <<<'EOT'
<x-slot name="foo" class="font-bold">
</x-slot>
EOT;

    $expected = <<<'EXPECTED'
 @slot('foo', null, ['class' => 'font-bold']) 
 @endslot
EXPECTED;

    $result = compiler()->compile($template);

    expect($result)->toBe($expected);
});

test('slots with dynamic attributes can be compiled', function () {
    $template = <<<'EOT'
<x-slot name="foo" :class="$classes">
</x-slot>
EOT;

    $expected = <<<'EXPECTED'
 @slot('foo', null, ['class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($classes)]) 
 @endslot
EXPECTED;

    $result = compiler()->compile($template);

    expect($result)->toBe($expected);
});

test('basic component parsing', function () {
    $template = <<<'EOT'
<div><x-alert type="foo" limit="5" @click="foo" wire:click="changePlan('{{ $plan }}')" required /><x-alert /></div>
EOT;

    $expected = <<<'EXPECTED'
<div>##BEGIN-COMPONENT-CLASS##@component('Stillat\BladeParser\Tests\Compiler\TestAlertComponent', 'alert', [])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Stillat\BladeParser\Tests\Compiler\TestAlertComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'foo','limit' => '5','@click' => 'foo','wire:click' => 'changePlan(\''.e($plan).'\')','required' => true]); ?>
@endComponentClass##END-COMPONENT-CLASS####BEGIN-COMPONENT-CLASS##@component('Stillat\BladeParser\Tests\Compiler\TestAlertComponent', 'alert', [])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Stillat\BladeParser\Tests\Compiler\TestAlertComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
@endComponentClass##END-COMPONENT-CLASS##</div>
EXPECTED;

    $result = compiler([
        'alert' => TestAlertComponent::class,
    ])->compileTags($template);

    expect(StringUtilities::normalizeLineEndings($result))->toBe(StringUtilities::normalizeLineEndings($expected));
});

test('basic component with empty attributes parsing', function () {
    $template = <<<'EOT'
<div><x-alert type="" limit='' @click="" required /></div>
EOT;

    $expected = <<<'EXPECTED'
<div>##BEGIN-COMPONENT-CLASS##@component('Stillat\BladeParser\Tests\Compiler\TestAlertComponent', 'alert', [])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Stillat\BladeParser\Tests\Compiler\TestAlertComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => '','limit' => '','@click' => '','required' => true]); ?>
@endComponentClass##END-COMPONENT-CLASS##</div>
EXPECTED;

    $result = compiler([
        'alert' => TestAlertComponent::class,
    ])->compile($template);

    expect(StringUtilities::normalizeLineEndings($result))->toBe(StringUtilities::normalizeLineEndings($expected));
});

test('data camel casing', function () {
    $template = <<<'EOT'
<x-profile user-id="1"></x-profile>
EOT;

    $expected = <<<'EXPECTED'
##BEGIN-COMPONENT-CLASS##@component('Stillat\BladeParser\Tests\Compiler\TestProfileComponent', 'profile', ['userId' => '1'])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Stillat\BladeParser\Tests\Compiler\TestProfileComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?> @endComponentClass##END-COMPONENT-CLASS##
EXPECTED;

    $result = compiler(['profile' => TestProfileComponent::class])->compile($template);

    expect(StringUtilities::normalizeLineEndings($result))->toBe(StringUtilities::normalizeLineEndings($expected));
});

test('colon data', function () {
    $template = <<<'EOT'
<x-profile :user-id="1"></x-profile>
EOT;

    $expected = <<<'EXPECTED'
##BEGIN-COMPONENT-CLASS##@component('Stillat\BladeParser\Tests\Compiler\TestProfileComponent', 'profile', ['userId' => 1])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Stillat\BladeParser\Tests\Compiler\TestProfileComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?> @endComponentClass##END-COMPONENT-CLASS##
EXPECTED;

    $result = compiler(['profile' => TestProfileComponent::class])->compile($template);

    expect(StringUtilities::normalizeLineEndings($result))->toBe(StringUtilities::normalizeLineEndings($expected));
});

test('escaped colon attribute', function () {
    $template = <<<'EOT'
<x-profile :user-id="1" ::title="user.name"></x-profile>
EOT;

    $expected = <<<'EXPECTED'
##BEGIN-COMPONENT-CLASS##@component('Stillat\BladeParser\Tests\Compiler\TestProfileComponent', 'profile', ['userId' => 1])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Stillat\BladeParser\Tests\Compiler\TestProfileComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([':title' => 'user.name']); ?> @endComponentClass##END-COMPONENT-CLASS##
EXPECTED;

    $result = compiler(['profile' => TestProfileComponent::class])->compile($template);

    expect(StringUtilities::normalizeLineEndings($result))->toBe(StringUtilities::normalizeLineEndings($expected));
});

test('colon attributes is escaped if strings', function () {
    $template = <<<'EOT'
<x-profile :src="'foo'"></x-profile>
EOT;

    $expected = <<<'EXPECTED'
##BEGIN-COMPONENT-CLASS##@component('Stillat\BladeParser\Tests\Compiler\TestProfileComponent', 'profile', [])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Stillat\BladeParser\Tests\Compiler\TestProfileComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('foo')]); ?> @endComponentClass##END-COMPONENT-CLASS##
EXPECTED;

    $result = compiler(['profile' => TestProfileComponent::class])->compile($template);

    expect(StringUtilities::normalizeLineEndings($result))->toBe(StringUtilities::normalizeLineEndings($expected));
});

test('colon nested component parsing', function () {
    $template = <<<'EOT'
<x-foo:alert></x-foo:alert>
EOT;

    $expected = <<<'EXPECTED'
##BEGIN-COMPONENT-CLASS##@component('Stillat\BladeParser\Tests\Compiler\TestAlertComponent', 'foo:alert', [])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Stillat\BladeParser\Tests\Compiler\TestAlertComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?> @endComponentClass##END-COMPONENT-CLASS##
EXPECTED;

    $result = compiler(['foo:alert' => TestAlertComponent::class])->compile($template);

    expect(StringUtilities::normalizeLineEndings($result))->toBe(StringUtilities::normalizeLineEndings($expected));
});

test('colon starting nested component parsing', function () {
    $template = <<<'EOT'
<x:foo:alert></x-foo:alert>
EOT;

    $expected = <<<'EXPECTED'
##BEGIN-COMPONENT-CLASS##@component('Stillat\BladeParser\Tests\Compiler\TestAlertComponent', 'foo:alert', [])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Stillat\BladeParser\Tests\Compiler\TestAlertComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?> @endComponentClass##END-COMPONENT-CLASS##
EXPECTED;

    $result = compiler(['foo:alert' => TestAlertComponent::class])->compile($template);

    expect(StringUtilities::normalizeLineEndings($result))->toBe(StringUtilities::normalizeLineEndings($expected));
});

test('self closing components can be compiled', function () {
    $template = <<<'EOT'
<div><x-alert/></div>
EOT;

    $expected = <<<'EXPECTED'
<div>##BEGIN-COMPONENT-CLASS##@component('Stillat\BladeParser\Tests\Compiler\TestAlertComponent', 'alert', [])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Stillat\BladeParser\Tests\Compiler\TestAlertComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
@endComponentClass##END-COMPONENT-CLASS##</div>
EXPECTED;

    $result = compiler(['alert' => TestAlertComponent::class])->compile($template);

    expect(StringUtilities::normalizeLineEndings($result))->toBe(StringUtilities::normalizeLineEndings($expected));
});

test('class names can be guessed', function () {
    $container = new Container;
    $container->instance(Application::class, $app = m::mock(Application::class));
    $app->shouldReceive('getNamespace')->andReturn('App\\');
    Container::setInstance($container);

    $result = compiler()->guessClassName('alert');

    expect(trim($result))->toBe("App\View\Components\Alert");

    Container::setInstance(null);
});

test('class names can be guessed with namespaces', function () {
    $container = new Container;
    $container->instance(Application::class, $app = m::mock(Application::class));
    $app->shouldReceive('getNamespace')->andReturn('App\\');
    Container::setInstance($container);

    $result = compiler()->guessClassName('base.alert');

    expect(trim($result))->toBe("App\View\Components\Base\Alert");

    Container::setInstance(null);
});

test('components can be compiled with hyphen attributes', function () {
    mockViewFactory();

    $template = <<<'EOT'
<x-alert class="bar" wire:model="foo" x-on:click="bar" @click="baz" />
EOT;

    $expected = <<<'EXPECTED'
##BEGIN-COMPONENT-CLASS##@component('Stillat\BladeParser\Tests\Compiler\TestAlertComponent', 'alert', [])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Stillat\BladeParser\Tests\Compiler\TestAlertComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'bar','wire:model' => 'foo','x-on:click' => 'bar','@click' => 'baz']); ?>
@endComponentClass##END-COMPONENT-CLASS##
EXPECTED;

    $result = compiler(['alert' => TestAlertComponent::class])->compile($template);

    expect(StringUtilities::normalizeLineEndings($result))->toBe(StringUtilities::normalizeLineEndings($expected));
});

test('self closing components can be compiled with data and attributes', function () {
    mockViewFactory();

    $template = <<<'EOT'
<x-alert title="foo" class="bar" wire:model="foo" />
EOT;

    $expected = <<<'EXPECTED'
##BEGIN-COMPONENT-CLASS##@component('Stillat\BladeParser\Tests\Compiler\TestAlertComponent', 'alert', ['title' => 'foo'])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Stillat\BladeParser\Tests\Compiler\TestAlertComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'bar','wire:model' => 'foo']); ?>
@endComponentClass##END-COMPONENT-CLASS##
EXPECTED;

    $result = compiler(['alert' => TestAlertComponent::class])->compile($template);

    expect(StringUtilities::normalizeLineEndings($result))->toBe(StringUtilities::normalizeLineEndings($expected));
});

test('component can receive attribute bag', function () {
    $template = <<<'TEMPLATE'
<x-profile class="bar" {{ $attributes }} wire:model="foo"></x-profile>
TEMPLATE;

    $expected = <<<'EXPECTED'
##BEGIN-COMPONENT-CLASS##@component('Stillat\BladeParser\Tests\Compiler\TestProfileComponent', 'profile', [])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Stillat\BladeParser\Tests\Compiler\TestProfileComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'bar','attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes),'wire:model' => 'foo']); ?> @endComponentClass##END-COMPONENT-CLASS##
EXPECTED;

    $result = compiler(['profile' => TestProfileComponent::class])->compile($template);

    expect(StringUtilities::normalizeLineEndings($result))->toBe(StringUtilities::normalizeLineEndings($expected));
});

test('self closing component can receive attribute bag', function () {
    $template = <<<'EOT'
<div><x-alert title="foo" class="bar" {{ $attributes->merge(['class' => 'test']) }} wire:model="foo" /></div>
EOT;

    $expected = <<<'EXPECTED'
<div>##BEGIN-COMPONENT-CLASS##@component('Stillat\BladeParser\Tests\Compiler\TestAlertComponent', 'alert', ['title' => 'foo'])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Stillat\BladeParser\Tests\Compiler\TestAlertComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'bar','attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes->merge(['class' => 'test'])),'wire:model' => 'foo']); ?>
@endComponentClass##END-COMPONENT-CLASS##</div>
EXPECTED;

    $result = compiler(['alert' => TestAlertComponent::class])->compile($template);

    expect(StringUtilities::normalizeLineEndings($result))->toBe(StringUtilities::normalizeLineEndings($expected));
});

test('components can have attached word', function () {
    mockViewFactory();

    $template = <<<'EOT'
<x-profile></x-profile>Words
EOT;

    $expected = <<<'EXPECTED'
##BEGIN-COMPONENT-CLASS##@component('Stillat\BladeParser\Tests\Compiler\TestProfileComponent', 'profile', [])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Stillat\BladeParser\Tests\Compiler\TestProfileComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?> @endComponentClass##END-COMPONENT-CLASS##Words
EXPECTED;

    $result = compiler(['profile' => TestProfileComponent::class])->compile($template);

    expect(StringUtilities::normalizeLineEndings($result))->toBe(StringUtilities::normalizeLineEndings($expected));
});

test('self closing components can have attached word', function () {
    $template = <<<'EOT'
<x-alert/>Words
EOT;

    $expected = <<<'EXPECTED'
##BEGIN-COMPONENT-CLASS##@component('Stillat\BladeParser\Tests\Compiler\TestAlertComponent', 'alert', [])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Stillat\BladeParser\Tests\Compiler\TestAlertComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
@endComponentClass##END-COMPONENT-CLASS##Words
EXPECTED;

    $result = compiler(['alert' => TestAlertComponent::class])->compile($template);

    expect(StringUtilities::normalizeLineEndings($result))->toBe(StringUtilities::normalizeLineEndings($expected));
});

test('self closing components can be compiled with bound data', function () {
    mockViewFactory();

    $template = <<<'EOT'
<x-alert :title="$title" class="bar" />
EOT;

    $expected = <<<'EXPECTED'
##BEGIN-COMPONENT-CLASS##@component('Stillat\BladeParser\Tests\Compiler\TestAlertComponent', 'alert', ['title' => $title])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Stillat\BladeParser\Tests\Compiler\TestAlertComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'bar']); ?>
@endComponentClass##END-COMPONENT-CLASS##
EXPECTED;

    $result = compiler(['alert' => TestAlertComponent::class])->compile($template);

    expect(StringUtilities::normalizeLineEndings($result))->toBe(StringUtilities::normalizeLineEndings($expected));
});

test('paired component tags', function () {
    mockViewFactory();

    $template = <<<'EOT'
<x-alert>
</x-alert>
EOT;

    $expected = <<<'EXPECTED'
##BEGIN-COMPONENT-CLASS##@component('Stillat\BladeParser\Tests\Compiler\TestAlertComponent', 'alert', [])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Stillat\BladeParser\Tests\Compiler\TestAlertComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
 @endComponentClass##END-COMPONENT-CLASS##
EXPECTED;

    $result = compiler(['alert' => TestAlertComponent::class])->compile($template);

    expect(StringUtilities::normalizeLineEndings($result))->toBe(StringUtilities::normalizeLineEndings($expected));
});

test('classless components', function () {
    $container = new Container;
    $container->instance(Application::class, $app = m::mock(Application::class));
    $container->instance(Factory::class, $factory = m::mock(Factory::class));
    $app->shouldReceive('getNamespace')->once()->andReturn('App\\');
    $factory->shouldReceive('exists')->once()->andReturn(true);
    Container::setInstance($container);

    $template = <<<'EOT'
<x-anonymous-component :name="'Taylor'" :age="31" wire:model="foo" />
EOT;

    $expected = <<<'EXPECTED'
##BEGIN-COMPONENT-CLASS##@component('Illuminate\View\AnonymousComponent', 'anonymous-component', ['view' => 'components.anonymous-component','data' => ['name' => 'Taylor','age' => 31,'wire:model' => 'foo']])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Taylor'),'age' => 31,'wire:model' => 'foo']); ?>
@endComponentClass##END-COMPONENT-CLASS##
EXPECTED;

    $result = compiler()->compile($template);

    expect(StringUtilities::normalizeLineEndings($result))->toBe(StringUtilities::normalizeLineEndings($expected));
});

test('classless components with index view', function () {
    $container = new Container;
    $container->instance(Application::class, $app = m::mock(Application::class));
    $container->instance(Factory::class, $factory = m::mock(Factory::class));
    $app->shouldReceive('getNamespace')->andReturn('App\\');
    $factory->shouldReceive('exists')->andReturn(false, true);
    Container::setInstance($container);

    $template = <<<'EOT'
<x-anonymous-component :name="'Taylor'" :age="31" wire:model="foo" />
EOT;

    $expected = <<<'EXPECTED'
##BEGIN-COMPONENT-CLASS##@component('Illuminate\View\AnonymousComponent', 'anonymous-component', ['view' => 'components.anonymous-component.index','data' => ['name' => 'Taylor','age' => 31,'wire:model' => 'foo']])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Taylor'),'age' => 31,'wire:model' => 'foo']); ?>
@endComponentClass##END-COMPONENT-CLASS##
EXPECTED;

    $result = compiler()->compile($template);

    expect(StringUtilities::normalizeLineEndings($result))->toBe(StringUtilities::normalizeLineEndings($expected));
});

test('packages classless components', function () {
    $container = new Container;
    $container->instance(Application::class, $app = m::mock(Application::class));
    $container->instance(Factory::class, $factory = m::mock(Factory::class));
    $app->shouldReceive('getNamespace')->andReturn('App\\');
    $factory->shouldReceive('exists')->andReturn(true);
    Container::setInstance($container);

    $template = <<<'EOT'
<x-package::anonymous-component :name="'Taylor'" :age="31" wire:model="foo" />
EOT;

    $expected = <<<'EXPECTED'
##BEGIN-COMPONENT-CLASS##@component('Illuminate\View\AnonymousComponent', 'package::anonymous-component', ['view' => 'package::components.anonymous-component','data' => ['name' => 'Taylor','age' => 31,'wire:model' => 'foo']])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Taylor'),'age' => 31,'wire:model' => 'foo']); ?>
@endComponentClass##END-COMPONENT-CLASS##
EXPECTED;

    $result = compiler()->compile($template);

    expect(StringUtilities::normalizeLineEndings($result))->toBe(StringUtilities::normalizeLineEndings($expected));
});

test('classless components with anonymous component namespaces', function () {
    $container = new Container;

    $container->instance(Application::class, $app = m::mock(Application::class));
    $container->instance(Factory::class, $factory = m::mock(Factory::class));

    $app->shouldReceive('getNamespace')->once()->andReturn('App\\');
    $factory->shouldReceive('exists')->times(3)->andReturnUsing(function ($arg) {
        // In our test, we'll do as if the 'public.frontend.anonymous-component'
        // view exists and not the others.
        return $arg === 'public.frontend.anonymous-component';
    });

    Container::setInstance($container);

    $compiler = compiler();
    $compiler->setAnonymousComponentNamespaces([
        'frontend' => 'public.frontend',
    ]);

    $template = <<<'EOT'
<x-frontend::anonymous-component :name="'Taylor'" :age="31" wire:model="foo" />
EOT;

    $expected = <<<'EXPECTED'
##BEGIN-COMPONENT-CLASS##@component('Illuminate\View\AnonymousComponent', 'frontend::anonymous-component', ['view' => 'public.frontend.anonymous-component','data' => ['name' => 'Taylor','age' => 31,'wire:model' => 'foo']])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Taylor'),'age' => 31,'wire:model' => 'foo']); ?>
@endComponentClass##END-COMPONENT-CLASS##
EXPECTED;

    $result = $compiler->compile($template);

    expect(StringUtilities::normalizeLineEndings($result))->toBe(StringUtilities::normalizeLineEndings($expected));
});

test('classless components with anonymous component namespace with index view', function () {
    $container = new Container;

    $container->instance(Application::class, $app = m::mock(Application::class));
    $container->instance(Factory::class, $factory = m::mock(Factory::class));

    $app->shouldReceive('getNamespace')->once()->andReturn('App\\');
    $factory->shouldReceive('exists')->times(4)->andReturnUsing(function (string $viewNameBeingCheckedForExistence) {
        // In our test, we'll do as if the 'public.frontend.anonymous-component'
        // view exists and not the others.
        return $viewNameBeingCheckedForExistence === 'admin.auth.components.anonymous-component.index';
    });

    Container::setInstance($container);

    $compiler = compiler();
    $compiler->setAnonymousComponentNamespaces([
        'admin.auth' => 'admin.auth.components',
    ]);

    $template = <<<'EOT'
<x-admin.auth::anonymous-component :name="'Taylor'" :age="31" wire:model="foo" />
EOT;

    $expected = <<<'EXPECTED'
##BEGIN-COMPONENT-CLASS##@component('Illuminate\View\AnonymousComponent', 'admin.auth::anonymous-component', ['view' => 'admin.auth.components.anonymous-component.index','data' => ['name' => 'Taylor','age' => 31,'wire:model' => 'foo']])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Taylor'),'age' => 31,'wire:model' => 'foo']); ?>
@endComponentClass##END-COMPONENT-CLASS##
EXPECTED;

    $result = $compiler->compile($template);

    expect(StringUtilities::normalizeLineEndings($result))->toBe(StringUtilities::normalizeLineEndings($expected));
});

test('classless components with anonymous component path', function () {
    $container = new Container;

    $container->instance(Application::class, $app = m::mock(Application::class));
    $container->instance(Factory::class, $factory = m::mock(Factory::class));

    $app->shouldReceive('getNamespace')->once()->andReturn('App\\');

    $factory->shouldReceive('exists')->andReturnUsing(function ($arg) {
        return $arg === md5('test-directory').'::panel.index';
    });

    Container::setInstance($container);

    $compiler = compiler();
    $compiler->setAnonymousComponentPaths([
        ['path' => 'test-directory', 'prefix' => null, 'prefixHash' => md5('test-directory')],
    ]);

    $expected = <<<'EXPECTED'
##BEGIN-COMPONENT-CLASS##@component('Illuminate\View\AnonymousComponent', 'panel', ['view' => '8ee975052836fdc7da2267cf8a580b80::panel.index','data' => []])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
@endComponentClass##END-COMPONENT-CLASS##
EXPECTED;

    $result = $compiler->compileTags('<x-panel />');

    expect(StringUtilities::normalizeLineEndings($result))->toBe(StringUtilities::normalizeLineEndings($expected));
});

test('compiling raw echo inside parameter content', function () {
    mockViewFactory();

    $template = <<<'EOT'
<x-alert {!! $attributes !!} wire:model="foo" />
EOT;
    $expected = <<<'EXPECTED'
##BEGIN-COMPONENT-CLASS##@component('Stillat\BladeParser\Tests\Compiler\TestAlertComponent', 'alert', [])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Stillat\BladeParser\Tests\Compiler\TestAlertComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes),'wire:model' => 'foo']); ?>
@endComponentClass##END-COMPONENT-CLASS##
EXPECTED;

    expect(StringUtilities::normalizeLineEndings(compiler(['alert' => TestAlertComponent::class])->compile($template)))->toBe(StringUtilities::normalizeLineEndings($expected));
});

test('compiling triple echo inside parameter content', function () {
    mockViewFactory();

    $template = <<<'EOT'
<x-alert {{{ $attributes }}} wire:model="foo" />
EOT;
    $expected = <<<'EXPECTED'
##BEGIN-COMPONENT-CLASS##@component('Stillat\BladeParser\Tests\Compiler\TestAlertComponent', 'alert', [])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Stillat\BladeParser\Tests\Compiler\TestAlertComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes),'wire:model' => 'foo']); ?>
@endComponentClass##END-COMPONENT-CLASS##
EXPECTED;

    expect(StringUtilities::normalizeLineEndings(compiler(['alert' => TestAlertComponent::class])->compile($template)))->toBe(StringUtilities::normalizeLineEndings($expected));
});

test('classless index components with anonymous component path', function () {
    $container = new Container;

    $container->instance(Application::class, $app = m::mock(Application::class));
    $container->instance(Factory::class, $factory = m::mock(Factory::class));

    $app->shouldReceive('getNamespace')->once()->andReturn('App\\');

    $factory->shouldReceive('exists')->andReturnUsing(function ($arg) {
        return $arg === md5('test-directory').'::panel';
    });

    Container::setInstance($container);

    $compiler = compiler();
    $compiler->setAnonymousComponentPaths([
        ['path' => 'test-directory', 'prefix' => null, 'prefixHash' => md5('test-directory')],
    ]);

    $expected = <<<'EXPECTED'
##BEGIN-COMPONENT-CLASS##@component('Illuminate\View\AnonymousComponent', 'panel', ['view' => '8ee975052836fdc7da2267cf8a580b80::panel','data' => []])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
@endComponentClass##END-COMPONENT-CLASS##
EXPECTED;

    $result = $compiler->compileTags('<x-panel />');

    expect(StringUtilities::normalizeLineEndings($result))->toBe(StringUtilities::normalizeLineEndings($expected));
});

test('it throws an exception for non existing aliases', function () {
    mockViewFactory(false);

    $this->expectException(InvalidArgumentException::class);

    compiler(['alert' => 'foo.bar'])->compileTags('<x-alert />');
});

test('it throws an exception for non existing class', function () {
    $container = new Container;
    $container->instance(Application::class, $app = m::mock(Application::class));
    $container->instance(Factory::class, $factory = m::mock(Factory::class));
    $app->shouldReceive('getNamespace')->once()->andReturn('App\\');
    $factory->shouldReceive('exists')->twice()->andReturn(false);
    Container::setInstance($container);

    $this->expectException(InvalidArgumentException::class);

    compiler()->compileTags('<x-alert />');
});

test('attributes treated as props are removed from final attributes', function () {
    $container = new Container;
    $container->instance(Application::class, $app = m::mock(Application::class));
    $container->instance(Factory::class, $factory = m::mock(Factory::class));
    $container->alias(Factory::class, 'view');
    $app->shouldReceive('getNamespace')->never()->andReturn('App\\');
    $factory->shouldReceive('exists')->never();

    Container::setInstance($container);

    $attributes = new ComponentAttributeBag(['userId' => 'bar', 'other' => 'ok']);

    $component = m::mock(Component::class);
    $component->shouldReceive('withName')->with('profile')->once();
    $component->shouldReceive('shouldRender')->once()->andReturn(true);
    $component->shouldReceive('resolveView')->once()->andReturn('');
    $component->shouldReceive('data')->once()->andReturn([]);
    $component->shouldReceive('withAttributes')->once();

    Component::resolveComponentsUsing(fn () => $component);

    $__env = m::mock(\Illuminate\View\Factory::class);
    $__env->shouldReceive('startComponent')->once();
    $__env->shouldReceive('renderComponent')->once();

    $template = compiler(['profile' => TestProfileComponent::class])->compileTags('<x-profile {{ $attributes }} />');
    $template = $this->compiler->compileString($template);

    ob_start();
    eval(" ?> $template <?php ");
    ob_get_clean();

    expect('bar')->toBe($attributes->get('userId'));
    expect('ok')->toBe($attributes->get('other'));
});

test('custom component tag names can be compiled', function () {
    mockViewFactory();

    $template = <<<'EOT'
<custom-alert {!! $attributes !!} wire:model="foo" />
EOT;
    $expected = <<<'EXPECTED'
##BEGIN-COMPONENT-CLASS##@component('Stillat\BladeParser\Tests\Compiler\TestAlertComponent', 'alert', [])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Stillat\BladeParser\Tests\Compiler\TestAlertComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes),'wire:model' => 'foo']); ?>
@endComponentClass##END-COMPONENT-CLASS##
EXPECTED;

    expect(StringUtilities::normalizeLineEndings(compiler(['alert' => TestAlertComponent::class])->registerCustomComponentTag('custom')->compile($template)))->toBe(StringUtilities::normalizeLineEndings($expected));
});

test('custom components can be compiled with acustom compiler', function () {
    mockViewFactory();

    $compiler = compiler(['alert' => TestAlertComponent::class]);

    $compiler->registerCustomCompiler('custom', (new class implements CustomComponentTagCompiler
    {
        public function compile(ComponentNode $component): string
        {
            if ($component->isClosingTag && ! $component->isSelfClosing) {
                return 'Just a closing tag.';
            }

            $theValue = $component->getParameter('wire:model')->value;

            return "Just an opening tag with [{$theValue}]";
        }
    }));

    $template = <<<'EOT'
<custom-alert {!! $attributes !!} wire:model="foo"></custom-alert>

<x-alert {!! $attributes !!} wire:model="foo"></x-alert>
EOT;
    $result = $compiler->compile($template);

    $expected = <<<'EOT'
Just an opening tag with [foo]Just a closing tag.

##BEGIN-COMPONENT-CLASS##@component('Stillat\BladeParser\Tests\Compiler\TestAlertComponent', 'alert', [])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Stillat\BladeParser\Tests\Compiler\TestAlertComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes),'wire:model' => 'foo']); ?> @endComponentClass##END-COMPONENT-CLASS##
EOT;
    expect(StringUtilities::normalizeLineEndings($expected))->toBe(StringUtilities::normalizeLineEndings($result));
});

test('custom components can be compiled and core components ignored', function () {
    mockViewFactory();

    $compiler = compiler(['alert' => TestAlertComponent::class])->setCompileCoreComponents(false);

    $compiler->registerCustomCompiler('custom', (new class implements CustomComponentTagCompiler
    {
        public function compile(ComponentNode $component): ?string
        {
            if ($component->isClosingTag && ! $component->isSelfClosing) {
                return 'Just a closing tag.';
            }

            $theValue = $component->getParameter('wire:model')->value;

            return "Just an opening tag with [{$theValue}]";
        }
    }));

    $template = <<<'EOT'
<custom-alert {!! $attributes !!} wire:model="foo"></custom-alert>

<x-alert {!! $attributes !!} wire:model="foo"></x-alert>
EOT;
    $result = $compiler->compile($template);

    $expected = <<<'EOT'
Just an opening tag with [foo]Just a closing tag.

<x-alert {!! $attributes !!} wire:model="foo"></x-alert>
EOT;
    expect(StringUtilities::normalizeLineEndings($result))->toBe(StringUtilities::normalizeLineEndings($expected));
});

test('returning null from a custom compiler results in default compiler behavior', function () {
    mockViewFactory();

    $compiler = compiler(['alert' => TestAlertComponent::class])->setCompileCoreComponents(false);

    $compiler->registerCustomCompiler('custom', (new class implements CustomComponentTagCompiler
    {
        public function compile(ComponentNode $component): ?string
        {
            $useDefault = $component->getParameter('use-default')->value;

            if ($useDefault == 'true') {
                return null;
            }

            return 'A custom compiler result.';
        }
    }));

    $template = <<<'EOT'
<custom-alert use-default="true" />

<custom-alert use-default="false" />
EOT;
    $result = $compiler->compile($template);

    $expected = <<<'EOT'
##BEGIN-COMPONENT-CLASS##@component('Stillat\BladeParser\Tests\Compiler\TestAlertComponent', 'alert', [])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Stillat\BladeParser\Tests\Compiler\TestAlertComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['use-default' => 'true']); ?>
@endComponentClass##END-COMPONENT-CLASS##

A custom compiler result.
EOT;

    expect(StringUtilities::normalizeLineEndings($result))->toBe(StringUtilities::normalizeLineEndings($expected));
});

test('name attribute can be used if using short slot names', function () {
    $blade = <<<'EOT'
<x-input-with-slot>
    <x-slot:input name="my_form_field" class="text-input-lg" data-test="data">Test</x-slot:input>
</x-input-with-slot>
EOT;

    $expected = <<<'EXP'
##BEGIN-COMPONENT-CLASS##@component('Stillat\BladeParser\Tests\Compiler\InputWithSlot', 'input-with-slot', [])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Stillat\BladeParser\Tests\Compiler\InputWithSlot::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     @slot('input', null, ['name' => 'my_form_field','class' => 'text-input-lg','data-test' => 'data']) Test @endslot
 @endComponentClass##END-COMPONENT-CLASS##
EXP;

    $result = compiler([
        'input-with-slot' => \Stillat\BladeParser\Tests\Compiler\InputWithSlot::class,
    ])->compile($blade);

    expect(StringUtilities::normalizeLineEndings($result))->toBe(StringUtilities::normalizeLineEndings($expected));
});

test('name attribute cant be used if not using short slot names', function () {
    $blade = <<<'EOT'
<x-input-with-slot>
    <x-slot name="input" class="text-input-lg" data-test="data">Test</x-slot>
</x-input-with-slot>
EOT;

    $expected = <<<'EXP'
##BEGIN-COMPONENT-CLASS##@component('Stillat\BladeParser\Tests\Compiler\InputWithSlot', 'input-with-slot', [])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Stillat\BladeParser\Tests\Compiler\InputWithSlot::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     @slot('input', null, ['class' => 'text-input-lg','data-test' => 'data']) Test @endslot
 @endComponentClass##END-COMPONENT-CLASS##
EXP;

    $result = compiler([
        'input-with-slot' => \Stillat\BladeParser\Tests\Compiler\InputWithSlot::class,
    ])->compile($blade);

    expect(StringUtilities::normalizeLineEndings($result))->toBe(StringUtilities::normalizeLineEndings($expected));
});

test('bound name attribute can be used if using short slot names', function () {
    $blade = <<<'EOT'
<x-input-with-slot>
    <x-slot:input :name="'my_form_field'" class="text-input-lg" data-test="data">Test</x-slot:input>
</x-input-with-slot>
EOT;

    $expected = <<<'EXP'
##BEGIN-COMPONENT-CLASS##@component('Stillat\BladeParser\Tests\Compiler\InputWithSlot', 'input-with-slot', [])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Stillat\BladeParser\Tests\Compiler\InputWithSlot::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     @slot('input', null, ['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('my_form_field'),'class' => 'text-input-lg','data-test' => 'data']) Test @endslot
 @endComponentClass##END-COMPONENT-CLASS##
EXP;

    $result = compiler([
        'input-with-slot' => \Stillat\BladeParser\Tests\Compiler\InputWithSlot::class,
    ])->compile($blade);

    expect(StringUtilities::normalizeLineEndings($result))->toBe(StringUtilities::normalizeLineEndings($expected));
});

test('bound name attribute can be used if using short slot names and not first attribute', function () {
    $blade = <<<'EOT'
<x-input-with-slot>
    <x-slot:input class="text-input-lg" :name="'my_form_field'" data-test="data">Test</x-slot:input>
</x-input-with-slot>
EOT;

    $expected = <<<'EXP'
##BEGIN-COMPONENT-CLASS##@component('Stillat\BladeParser\Tests\Compiler\InputWithSlot', 'input-with-slot', [])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Stillat\BladeParser\Tests\Compiler\InputWithSlot::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     @slot('input', null, ['class' => 'text-input-lg','name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('my_form_field'),'data-test' => 'data']) Test @endslot
 @endComponentClass##END-COMPONENT-CLASS##
EXP;

    $result = compiler([
        'input-with-slot' => \Stillat\BladeParser\Tests\Compiler\InputWithSlot::class,
    ])->compile($blade);

    expect(StringUtilities::normalizeLineEndings($result))->toBe(StringUtilities::normalizeLineEndings($expected));
});

test('echo inside component parameter', function () {
    $blade = <<<'EOT'
<x-input-with-slot>
    <x-slot:input for="name" value="{{ __('Token Name') }}">Test</x-slot:input>
</x-input-with-slot>
EOT;

    $expected = <<<'EXP'
##BEGIN-COMPONENT-CLASS##@component('Stillat\BladeParser\Tests\Compiler\InputWithSlot', 'input-with-slot', [])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Stillat\BladeParser\Tests\Compiler\InputWithSlot::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     @slot('input', null, ['for' => 'name','value' => ''.e(__('Token Name')).'']) Test @endslot
 @endComponentClass##END-COMPONENT-CLASS##
EXP;

    $result = compiler([
        'input-with-slot' => \Stillat\BladeParser\Tests\Compiler\InputWithSlot::class,
    ])->compile($blade);

    expect(StringUtilities::normalizeLineEndings($result))->toBe(StringUtilities::normalizeLineEndings($expected));
});
