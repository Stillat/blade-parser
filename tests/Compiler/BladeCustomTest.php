<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('custom php code is correctly handled', function () {
    expect($this->compiler->compileString("@if(\$test) <?php @show('test'); ?> @endif"))->toBe('<?php if($test): ?> <?php @show(\'test\'); ?> <?php endif; ?>');
});

test('mixing yield and echo', function () {
    expect($this->compiler->compileString("@yield('title') - {{Config::get('site.title')}}"))->toBe('<?php echo $__env->yieldContent(\'title\'); ?> - <?php echo e(Config::get(\'site.title\')); ?>');
});

test('custom extensions are compiled', function () {
    $this->compiler->extend(function ($value) {
        return str_replace('foo', 'bar', $value);
    });
    expect($this->compiler->compileString('foo'))->toBe('bar');
});

test('custom statements', function () {
    expect($this->compiler->getCustomDirectives())->toHaveCount(0);
    $this->compiler->directive('customControl', function ($expression) {
        return "<?php echo custom_control({$expression}); ?>";
    });
    expect($this->compiler->getCustomDirectives())->toHaveCount(1);

    $string = '@if($foo)
@customControl(10, $foo, \'bar\')
@endif';
    $expected = '<?php if($foo): ?>
<?php echo custom_control(10, $foo, \'bar\'); ?>
<?php endif; ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('custom short statements', function () {
    $this->compiler->directive('customControl', function ($expression) {
        return '<?php echo custom_control(); ?>';
    });

    $string = '@customControl';
    $expected = '<?php echo custom_control(); ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('valid custom names', function () {
    expect($this->compiler->directive('custom', function () {
        //
    }))->toBeNull();
    expect($this->compiler->directive('custom_custom', function () {
        //
    }))->toBeNull();
    expect($this->compiler->directive('customCustom', function () {
        //
    }))->toBeNull();
    expect($this->compiler->directive('custom::custom', function () {
        //
    }))->toBeNull();
});

test('invalid custom names', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('The directive name [custom-custom] is not valid.');
    $this->compiler->directive('custom-custom', function () {
        //
    });
});

test('invalid custom names2', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('The directive name [custom:custom] is not valid.');
    $this->compiler->directive('custom:custom', function () {
        //
    });
});

test('custom extension overwrites core', function () {
    $this->compiler->directive('foreach', function ($expression) {
        return '<?php custom(); ?>';
    });

    $string = '@foreach';
    $expected = '<?php custom(); ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('custom conditions', function () {
    $this->compiler->if('custom', function ($user) {
        return true;
    });

    $string = '@custom($user)
@endcustom';
    $expected = '<?php if (\Illuminate\Support\Facades\Blade::check(\'custom\', $user)): ?>
<?php endif; ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('custom if else conditions', function () {
    $this->compiler->if('custom', function ($anything) {
        return true;
    });

    $string = '@custom($user)
@elsecustom($product)
@else
@endcustom';
    $expected = '<?php if (\Illuminate\Support\Facades\Blade::check(\'custom\', $user)): ?>
<?php elseif (\Illuminate\Support\Facades\Blade::check(\'custom\', $product)): ?>
<?php else: ?>
<?php endif; ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('custom unless conditions', function () {
    $this->compiler->if('custom', function ($anything) {
        return true;
    });

    $string = '@unlesscustom($user)
@endcustom';
    $expected = '<?php if (! \Illuminate\Support\Facades\Blade::check(\'custom\', $user)): ?>
<?php endif; ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('custom conditions accepts0 as argument', function () {
    $this->compiler->if('custom', function ($number) {
        return true;
    });

    $string = '@custom(0)
@elsecustom(0)
@endcustom';
    $expected = '<?php if (\Illuminate\Support\Facades\Blade::check(\'custom\', 0)): ?>
<?php elseif (\Illuminate\Support\Facades\Blade::check(\'custom\', 0)): ?>
<?php endif; ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('custom components', function () {
    $this->compiler->aliasComponent('app.components.alert', 'alert');

    $string = '@alert
@endalert';
    $expected = '<?php $__env->startComponent(\'app.components.alert\'); ?>
<?php echo $__env->renderComponent(); ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('custom components with slots', function () {
    $this->compiler->aliasComponent('app.components.alert', 'alert');

    $string = '@alert([\'type\' => \'danger\'])
@endalert';
    $expected = '<?php $__env->startComponent(\'app.components.alert\', [\'type\' => \'danger\']); ?>
<?php echo $__env->renderComponent(); ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('custom components with existing directive', function () {
    $this->compiler->aliasComponent('app.components.foreach', 'foreach');

    $string = '@foreach
@endforeach';
    $expected = '<?php $__env->startComponent(\'app.components.foreach\'); ?>
<?php echo $__env->renderComponent(); ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('custom includes', function () {
    $this->compiler->include('app.includes.input', 'input');

    $string = '@input';
    $expected = '<?php echo $__env->make(\'app.includes.input\', [], \Illuminate\Support\Arr::except(get_defined_vars(), [\'__data\', \'__path\']))->render(); ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('custom includes with data', function () {
    $this->compiler->include('app.includes.input', 'input');

    $string = '@input([\'type\' => \'email\'])';
    $expected = '<?php echo $__env->make(\'app.includes.input\', [\'type\' => \'email\'], \Illuminate\Support\Arr::except(get_defined_vars(), [\'__data\', \'__path\']))->render(); ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('custom includes default alias', function () {
    $this->compiler->include('app.includes.input');

    $string = '@input';
    $expected = '<?php echo $__env->make(\'app.includes.input\', [], \Illuminate\Support\Arr::except(get_defined_vars(), [\'__data\', \'__path\']))->render(); ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('custom includes with existing directive', function () {
    $this->compiler->include('app.includes.foreach');

    $string = '@foreach';
    $expected = '<?php echo $__env->make(\'app.includes.foreach\', [], \Illuminate\Support\Arr::except(get_defined_vars(), [\'__data\', \'__path\']))->render(); ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('unescaped non registered directive', function () {
    $string = '@media only screen and (min-width:480px) {';
    $expected = '@media only screen and (min-width:480px) {';
    expect($this->compiler->compileString($string))->toEqual($expected);
});
