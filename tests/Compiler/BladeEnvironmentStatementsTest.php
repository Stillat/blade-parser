<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('env statements are compiled', function () {
    $string = "@env('staging')
breeze
@else
boom
@endenv";
    $expected = "<?php if(app()->environment('staging')): ?>
breeze
<?php else: ?>
boom
<?php endif; ?>";
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('env statements with multiple string params are compiled', function () {
    $string = "@env('staging', 'production')
breeze
@else
boom
@endenv";
    $expected = "<?php if(app()->environment('staging', 'production')): ?>
breeze
<?php else: ?>
boom
<?php endif; ?>";
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('env statements with array param are compiled', function () {
    $string = "@env(['staging', 'production'])
breeze
@else
boom
@endenv";
    $expected = "<?php if(app()->environment(['staging', 'production'])): ?>
breeze
<?php else: ?>
boom
<?php endif; ?>";
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('production statements are compiled', function () {
    $string = '@production
breeze
@else
boom
@endproduction';
    $expected = "<?php if(app()->environment('production')): ?>
breeze
<?php else: ?>
boom
<?php endif; ?>";
    expect($this->compiler->compileString($string))->toEqual($expected);
});
