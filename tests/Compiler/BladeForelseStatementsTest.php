<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Illuminate\Contracts\View\ViewCompilationException;

test('forelse statements are compiled', function () {
    $string = '@forelse ($this->getUsers() as $user)
breeze
@empty
empty
@endforelse';
    $expected = '<?php $__empty_1 = true; $__currentLoopData = $this->getUsers(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
breeze
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
empty
<?php endif; ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('forelse statements are compiled with uppercase syntax', function () {
    $string = '@forelse ($this->getUsers() AS $user)
breeze
@empty
empty
@endforelse';
    $expected = '<?php $__empty_1 = true; $__currentLoopData = $this->getUsers(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
breeze
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
empty
<?php endif; ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('forelse statements are compiled with multiple line', function () {
    $string = '@forelse ([
foo,
bar,
] as $label)
breeze
@empty
empty
@endforelse';
    $expected = '<?php $__empty_1 = true; $__currentLoopData = [
foo,
bar,
]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
breeze
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
empty
<?php endif; ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('nested forelse statements are compiled', function () {
    $string = '@forelse ($this->getUsers() as $user)
@forelse ($user->tags as $tag)
breeze
@empty
tag empty
@endforelse
@empty
empty
@endforelse';
    $expected = '<?php $__empty_1 = true; $__currentLoopData = $this->getUsers(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<?php $__empty_2 = true; $__currentLoopData = $user->tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
breeze
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
tag empty
<?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
empty
<?php endif; ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('forelse statements throw humanized message when invalid statement', function ($initialStatement) {
    $this->expectException(ViewCompilationException::class);
    $this->expectExceptionMessage('Malformed @forelse statement.');
    $string = "$initialStatement
breeze
@empty
tag empty
@endforelse";
    $this->compiler->compileString($string);
})->with('invalidForelseStatementsDataProvider');

dataset('invalidForelseStatementsDataProvider', function () {
    return [
        ['@forelse'],
        ['@forelse()'],
        ['@forelse ()'],
        ['@forelse($test)'],
        ['@forelse($test as)'],
        ['@forelse(as)'],
        ['@forelse ( as )'],
    ];
});
