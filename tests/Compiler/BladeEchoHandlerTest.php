<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->compiler->stringable(function (Fluent $object) {
        return 'Hello World';
    });
});

test('blade handler can intercept regular echos', function () {
    expect($this->compiler->compileString('{{$exampleObject}}'))->toBe("<?php \$__bladeCompiler = app('blade.compiler'); ?><?php echo e(\$__bladeCompiler->applyEchoHandler(\$exampleObject)); ?>");
});

test('blade handler can intercept raw echos', function () {
    expect($this->compiler->compileString('{!!$exampleObject!!}'))->toBe("<?php \$__bladeCompiler = app('blade.compiler'); ?><?php echo \$__bladeCompiler->applyEchoHandler(\$exampleObject); ?>");
});

test('blade handler can intercept escaped echos', function () {
    expect($this->compiler->compileString('{{{$exampleObject}}}'))->toBe("<?php \$__bladeCompiler = app('blade.compiler'); ?><?php echo e(\$__bladeCompiler->applyEchoHandler(\$exampleObject)); ?>");
});

test('whitespace is preserved correctly', function () {
    expect($this->compiler->compileString("{{\$exampleObject}}\n"))->toBe("<?php \$__bladeCompiler = app('blade.compiler'); ?><?php echo e(\$__bladeCompiler->applyEchoHandler(\$exampleObject)); ?>\n");
});

test('handler logic works correctly', function ($blade) {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage('The fluent object has been successfully handled!');

    Blade::stringable(Fluent::class, function ($object) {
        throw new Exception('The fluent object has been successfully handled!');
    });
    $exampleObject = new Fluent;

    eval(Str::of($this->compiler->compileString($blade))->remove(['<?php', '?>']));
})->with('handlerLogicDataProvider');

dataset('handlerLogicDataProvider', function () {
    return [
        ['{{$exampleObject}}'],
        ['{{$exampleObject;}}'],
        ['{{{$exampleObject;}}}'],
        ['{!!$exampleObject;!!}'],
    ];
});

test('handler works with non stringables', function ($blade, $expectedOutput) {
    ob_start();
    eval(Str::of($this->compiler->compileString($blade))->remove(['<?php', '?>']));
    $output = ob_get_contents();
    ob_end_clean();

    expect($output)->toBe($expectedOutput);
})->with('nonStringableDataProvider');

dataset('nonStringableDataProvider', function () {
    return [
        ['{{"foo" . "bar"}}', 'foobar'],
        ['{{ 1 + 2 }}{{ "test"; }}', '3test'],
        ['@php($test = "hi"){{ $test }}', 'hi'],
        ['{!! "&nbsp;" !!}', '&nbsp;'],
    ];
});
