<?php

namespace Stillat\BladeParser\Tests\Compiler;

use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\Component;
use Illuminate\View\ComponentAttributeBag;
use InvalidArgumentException;
use Mockery as m;
use Stillat\BladeParser\Compiler\ComponentNodeCompiler;
use Stillat\BladeParser\Compiler\ComponentTagCompiler;
use Stillat\BladeParser\Contracts\CustomComponentTagCompiler;
use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Parser\DocumentParser;
use Stillat\BladeParser\Tests\ParserTestCase;

class BladeComponentTagCompilerTest extends ParserTestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testSlotsCanBeCompiled()
    {
        $template = <<<'EOT'
<x-slot name="foo">
</x-slot>
EOT;

        $expected = <<<'EXPECTED'
 @slot('foo', null, []) 
 @endslot
EXPECTED;

        $result = $this->compiler()->compileTags($template);

        $this->assertSame($expected, $result);
    }

    public function testInlineSlotsCanBeCompiled()
    {
        $template = <<<'EOT'
<x-slot:foo>
</x-slot>
EOT;

        $expected = <<<'EXPECTED'
 @slot('foo', null, []) 
 @endslot
EXPECTED;

        $result = $this->compiler()->compileTags($template);

        $this->assertSame($expected, $result);
    }

    public function testDynamicSlotsCanBeCompiled()
    {
        $template = <<<'EOT'
<x-slot :name="$foo">
</x-slot>
EOT;

        $expected = <<<'EXPECTED'
 @slot($foo, null, []) 
 @endslot
EXPECTED;

        $result = $this->compiler()->compileTags($template);

        $this->assertSame($expected, $result);
    }

    public function testSlotsWithAttributesCanBeCompiled()
    {
        $template = <<<'EOT'
<x-slot name="foo" class="font-bold">
</x-slot>
EOT;

        $expected = <<<'EXPECTED'
 @slot('foo', null, ['class' => 'font-bold']) 
 @endslot
EXPECTED;

        $result = $this->compiler()->compile($template);

        $this->assertSame($expected, $result);
    }

    public function testSlotsWithDynamicAttributesCanBeCompiled()
    {
        $template = <<<'EOT'
<x-slot name="foo" :class="$classes">
</x-slot>
EOT;

        $expected = <<<'EXPECTED'
 @slot('foo', null, ['class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($classes)]) 
 @endslot
EXPECTED;

        $result = $this->compiler()->compile($template);

        $this->assertSame($expected, $result);
    }

    public function testBasicComponentParsing()
    {
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

        $result = $this->compiler([
            'alert' => TestAlertComponent::class,
        ])->compileTags($template);

        $this->assertSame($expected, $result);
    }

    public function testBasicComponentWithEmptyAttributesParsing()
    {
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

        $result = $this->compiler([
            'alert' => TestAlertComponent::class,
        ])->compile($template);

        $this->assertSame($expected, $result);
    }

    public function testDataCamelCasing()
    {
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

        $result = $this->compiler(['profile' => TestProfileComponent::class])->compile($template);

        $this->assertSame($expected, $result);
    }

    public function testColonData()
    {
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

        $result = $this->compiler(['profile' => TestProfileComponent::class])->compile($template);

        $this->assertSame($expected, $result);
    }

    public function testEscapedColonAttribute()
    {
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

        $result = $this->compiler(['profile' => TestProfileComponent::class])->compile($template);

        $this->assertSame($expected, $result);
    }

    public function testColonAttributesIsEscapedIfStrings()
    {
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

        $result = $this->compiler(['profile' => TestProfileComponent::class])->compile($template);

        $this->assertSame($expected, $result);
    }

    public function testColonNestedComponentParsing()
    {
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

        $result = $this->compiler(['foo:alert' => TestAlertComponent::class])->compile($template);

        $this->assertSame($expected, $result);
    }

    public function testColonStartingNestedComponentParsing()
    {
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

        $result = $this->compiler(['foo:alert' => TestAlertComponent::class])->compile($template);

        $this->assertSame($expected, $result);
    }

    public function testSelfClosingComponentsCanBeCompiled()
    {
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

        $result = $this->compiler(['alert' => TestAlertComponent::class])->compile($template);

        $this->assertSame($expected, $result);
    }

    public function testClassNamesCanBeGuessed()
    {
        $container = new Container;
        $container->instance(Application::class, $app = m::mock(Application::class));
        $app->shouldReceive('getNamespace')->andReturn('App\\');
        Container::setInstance($container);

        $result = $this->compiler()->guessClassName('alert');

        $this->assertSame("App\View\Components\Alert", trim($result));

        Container::setInstance(null);
    }

    public function testClassNamesCanBeGuessedWithNamespaces()
    {
        $container = new Container;
        $container->instance(Application::class, $app = m::mock(Application::class));
        $app->shouldReceive('getNamespace')->andReturn('App\\');
        Container::setInstance($container);

        $result = $this->compiler()->guessClassName('base.alert');

        $this->assertSame("App\View\Components\Base\Alert", trim($result));

        Container::setInstance(null);
    }

    public function testComponentsCanBeCompiledWithHyphenAttributes()
    {
        $this->mockViewFactory();

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

        $result = $this->compiler(['alert' => TestAlertComponent::class])->compile($template);

        $this->assertSame($expected, $result);
    }

    public function testSelfClosingComponentsCanBeCompiledWithDataAndAttributes()
    {
        $this->mockViewFactory();

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

        $result = $this->compiler(['alert' => TestAlertComponent::class])->compile($template);

        $this->assertSame($expected, $result);
    }

    public function testComponentCanReceiveAttributeBag()
    {
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

        $result = $this->compiler(['profile' => TestProfileComponent::class])->compile($template);

        $this->assertSame($expected, $result);
    }

    public function testSelfClosingComponentCanReceiveAttributeBag()
    {
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

        $result = $this->compiler(['alert' => TestAlertComponent::class])->compile($template);

        $this->assertSame($expected, $result);
    }

    public function testComponentsCanHaveAttachedWord()
    {
        $this->mockViewFactory();

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

        $result = $this->compiler(['profile' => TestProfileComponent::class])->compile($template);

        $this->assertSame($expected, $result);
    }

    public function testSelfClosingComponentsCanHaveAttachedWord()
    {
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

        $result = $this->compiler(['alert' => TestAlertComponent::class])->compile($template);

        $this->assertSame($expected, $result);
    }

    public function testSelfClosingComponentsCanBeCompiledWithBoundData()
    {
        $this->mockViewFactory();

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

        $result = $this->compiler(['alert' => TestAlertComponent::class])->compile($template);

        $this->assertSame($expected, $result);
    }

    public function testPairedComponentTags()
    {
        $this->mockViewFactory();

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

        $result = $this->compiler(['alert' => TestAlertComponent::class])->compile($template);

        $this->assertSame($expected, $result);
    }

    public function testClasslessComponents()
    {
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

        $result = $this->compiler()->compile($template);

        $this->assertSame($expected, $result);
    }

    public function testClasslessComponentsWithIndexView()
    {
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

        $result = $this->compiler()->compile($template);

        $this->assertSame($expected, $result);
    }

    public function testPackagesClasslessComponents()
    {
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

        $result = $this->compiler()->compile($template);

        $this->assertSame($expected, $result);
    }

    public function testClasslessComponentsWithAnonymousComponentNamespaces()
    {
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

        $compiler = $this->compiler();
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

        $this->assertSame($expected, $result);
    }

    public function testClasslessComponentsWithAnonymousComponentNamespaceWithIndexView()
    {
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

        $compiler = $this->compiler();
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

        $this->assertSame($expected, $result);
    }

    public function testClasslessComponentsWithAnonymousComponentPath()
    {
        $container = new Container;

        $container->instance(Application::class, $app = m::mock(Application::class));
        $container->instance(Factory::class, $factory = m::mock(Factory::class));

        $app->shouldReceive('getNamespace')->once()->andReturn('App\\');

        $factory->shouldReceive('exists')->andReturnUsing(function ($arg) {
            return $arg === md5('test-directory').'::panel.index';
        });

        Container::setInstance($container);

        $compiler = $this->compiler();
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

        $this->assertSame($expected, $result);
    }

    public function testCompilingRawEchoInsideParameterContent()
    {
        $this->mockViewFactory();

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

        $this->assertSame($expected, $this->compiler(['alert' => TestAlertComponent::class])->compile($template));
    }

    public function testCompilingTripeEchoInsideParameterContent()
    {
        $this->mockViewFactory();

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

        $this->assertSame($expected, $this->compiler(['alert' => TestAlertComponent::class])->compile($template));
    }

    public function testClasslessIndexComponentsWithAnonymousComponentPath()
    {
        $container = new Container;

        $container->instance(Application::class, $app = m::mock(Application::class));
        $container->instance(Factory::class, $factory = m::mock(Factory::class));

        $app->shouldReceive('getNamespace')->once()->andReturn('App\\');

        $factory->shouldReceive('exists')->andReturnUsing(function ($arg) {
            return $arg === md5('test-directory').'::panel';
        });

        Container::setInstance($container);

        $compiler = $this->compiler();
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

        $this->assertSame($expected, $result);
    }

    public function testItThrowsAnExceptionForNonExistingAliases()
    {
        $this->mockViewFactory(false);

        $this->expectException(InvalidArgumentException::class);

        $this->compiler(['alert' => 'foo.bar'])->compileTags('<x-alert />');
    }

    public function testItThrowsAnExceptionForNonExistingClass()
    {
        $container = new Container;
        $container->instance(Application::class, $app = m::mock(Application::class));
        $container->instance(Factory::class, $factory = m::mock(Factory::class));
        $app->shouldReceive('getNamespace')->once()->andReturn('App\\');
        $factory->shouldReceive('exists')->twice()->andReturn(false);
        Container::setInstance($container);

        $this->expectException(InvalidArgumentException::class);

        $this->compiler()->compileTags('<x-alert />');
    }

    public function testAttributesTreatedAsPropsAreRemovedFromFinalAttributes()
    {
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

        $template = $this->compiler(['profile' => TestProfileComponent::class])->compileTags('<x-profile {{ $attributes }} />');
        $template = $this->compiler->compileString($template);

        ob_start();
        eval(" ?> $template <?php ");
        ob_get_clean();

        $this->assertNull($attributes->get('userId'));
        $this->assertSame($attributes->get('other'), 'ok');
    }

    public function testCustomComponentTagNamesCanBeCompiled()
    {
        $this->mockViewFactory();

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

        $this->assertSame($expected, $this->compiler(['alert' => TestAlertComponent::class])->registerCustomComponentTag('custom')->compile($template));
    }

    public function testCustomComponentsCanBeCompiledWithACustomCompiler()
    {
        $this->mockViewFactory();

        $compiler = $this->compiler(['alert' => TestAlertComponent::class]);

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
        $this->assertSame($result, $expected);
    }

    public function testCustomComponentsCanBeCompiledAndCoreComponentsIgnored()
    {
        $this->mockViewFactory();

        $compiler = $this->compiler(['alert' => TestAlertComponent::class])->setCompileCoreComponents(false);

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
        $this->assertSame($result, $expected);
    }

    public function testReturningNullFromACustomCompilerResultsInDefaultCompilerBehavior()
    {
        $this->mockViewFactory();

        $compiler = $this->compiler(['alert' => TestAlertComponent::class])->setCompileCoreComponents(false);

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

        $this->assertSame($expected, $result);
    }

    protected function mockViewFactory($existsSucceeds = true)
    {
        $container = new Container;
        $container->instance(Factory::class, $factory = m::mock(Factory::class));
        $factory->shouldReceive('exists')->andReturn($existsSucceeds);
        Container::setInstance($container);
    }

    protected function compiler(array $aliases = [], array $namespaces = []): ComponentTagCompiler
    {
        $compiler = new ComponentTagCompiler(
            new ComponentNodeCompiler(),
            new DocumentParser()
        );

        $compiler->setAliases($aliases);
        $compiler->setNamespaces($namespaces);

        return $compiler;
    }
}

class TestAlertComponent extends Component
{
    public $title;

    public function __construct($title = 'foo', $userId = 1)
    {
        $this->title = $title;
    }

    public function render()
    {
        return 'alert';
    }
}

class TestProfileComponent extends Component
{
    public $userId;

    public function __construct($userId = 'foo')
    {
        $this->userId = $userId;
    }

    public function render()
    {
        return 'profile';
    }
}
