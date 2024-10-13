<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('echos are compiled', function () {
    expect($this->compiler->compileString('@csrf'))->toBe('<?php echo csrf_field(); ?>');
    expect($this->compiler->compileString("@method('patch')"))->toBe('<?php echo method_field(\'patch\'); ?>');
    expect($this->compiler->compileString('@dd($var1)'))->toBe('<?php dd($var1); ?>');
    expect($this->compiler->compileString('@dd($var1, $var2)'))->toBe('<?php dd($var1, $var2); ?>');
    expect($this->compiler->compileString('@dump($var1, $var2)'))->toBe('<?php dump($var1, $var2); ?>');
    expect($this->compiler->compileString('@vite'))->toBe('<?php echo app(\'Illuminate\Foundation\Vite\')(); ?>');
    expect($this->compiler->compileString('@vite()'))->toBe('<?php echo app(\'Illuminate\Foundation\Vite\')(); ?>');
    expect($this->compiler->compileString('@vite(\'resources/js/app.js\')'))->toBe('<?php echo app(\'Illuminate\Foundation\Vite\')(\'resources/js/app.js\'); ?>');
    expect($this->compiler->compileString('@vite([\'resources/js/app.js\'])'))->toBe('<?php echo app(\'Illuminate\Foundation\Vite\')([\'resources/js/app.js\']); ?>');
    expect($this->compiler->compileString('@viteReactRefresh'))->toBe('<?php echo app(\'Illuminate\Foundation\Vite\')->reactRefresh(); ?>');
});
