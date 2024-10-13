<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Illuminate\Contracts\View\ViewCompilationException;

test('foreach statements are compiled', function () {
    $string = '@foreach ($this->getUsers() as $user)
test
@endforeach';
    $expected = '<?php $__currentLoopData = $this->getUsers(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
test
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('foreach statements are compile with uppercase syntax', function () {
    $string = '@foreach ($this->getUsers() AS $user)
test
@endforeach';
    $expected = '<?php $__currentLoopData = $this->getUsers(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
test
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('foreach statements are compile with multiple line', function () {
    $string = '@foreach ([
foo,
bar,
] as $label)
test
@endforeach';
    $expected = '<?php $__currentLoopData = [
foo,
bar,
]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
test
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('nested foreach statements are compiled', function () {
    $string = '@foreach ($this->getUsers() as $user)
user info
@foreach ($user->tags as $tag)
tag info
@endforeach
@endforeach';
    $expected = '<?php $__currentLoopData = $this->getUsers(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
user info
<?php $__currentLoopData = $user->tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
tag info
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('loop content holder is extracted from foreach statements', function () {
    $string = '@foreach ($some_uSers1 as $user)';
    $expected = '<?php $__currentLoopData = $some_uSers1; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);

    $string = '@foreach ($users->get() as $user)';
    $expected = '<?php $__currentLoopData = $users->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);

    $string = '@foreach (range(1, 4) as $user)';
    $expected = '<?php $__currentLoopData = range(1, 4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);

    $string = '@foreach (   $users as $user)';
    $expected = '<?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);

    $string = '@foreach ($tasks as $task)';
    $expected = '<?php $__currentLoopData = $tasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);

    $string = "@foreach(resolve('App\\\\DataProviders\\\\'.\$provider)->data() as \$key => \$value)
    <input {{ \$foo ? 'bar': 'baz' }}>
@endforeach";
    $expected = "<?php \$__currentLoopData = resolve('App\\\\DataProviders\\\\'.\$provider)->data(); \$__env->addLoop(\$__currentLoopData); foreach(\$__currentLoopData as \$key => \$value): \$__env->incrementLoopIndices(); \$loop = \$__env->getLastLoop(); ?>
    <input <?php echo e(\$foo ? 'bar': 'baz'); ?>>
<?php endforeach; \$__env->popLoop(); \$loop = \$__env->getLastLoop(); ?>";

    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('foreach statements throw humanized message when invalid statement', function ($initialStatement) {
    $this->expectException(ViewCompilationException::class);
    $this->expectExceptionMessage('Malformed @foreach statement.');
    $string = "$initialStatement
test
@endforeach";
    $this->compiler->compileString($string);
})->with('invalidForeachStatementsDataProvider');

dataset('invalidForeachStatementsDataProvider', function () {
    return [
        ['@foreach'],
        ['@foreach()'],
        ['@foreach ()'],
        ['@foreach($test)'],
        ['@foreach($test as)'],
        ['@foreach(as)'],
        ['@foreach ( as )'],
    ];
});
