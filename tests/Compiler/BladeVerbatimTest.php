<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('verbatim blocks are compiled', function () {
    $string = '@verbatim {{ $a }} @if($b) {{ $b }} @endif @endverbatim';
    $expected = ' {{ $a }} @if($b) {{ $b }} @endif ';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('verbatim blocks with multiple lines are compiled', function () {
    $string = 'Some text
@verbatim
    {{ $a }}
    @if($b)
        {{ $b }}
    @endif
@endverbatim';
    $expected = 'Some text

    {{ $a }}
    @if($b)
        {{ $b }}
    @endif
';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('multiple verbatim blocks are compiled', function () {
    $string = '@verbatim {{ $a }} @endverbatim {{ $b }} @verbatim {{ $c }} @endverbatim';
    $expected = ' {{ $a }}  <?php echo e($b); ?>  {{ $c }} ';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('raw blocks are rendered in the right order', function () {
    $string = '@php echo "#1"; @endphp @verbatim {{ #2 }} @endverbatim @verbatim {{ #3 }} @endverbatim @php echo "#4"; @endphp';

    $expected = '<?php echo "#1"; ?>  {{ #2 }}   {{ #3 }}  <?php echo "#4"; ?>';

    expect($this->compiler->compileString($string))->toBe($expected);
});

test('multiline templates with raw blocks are rendered in the right order', function () {
    $string = '{{ $first }}
@php
    echo $second;
@endphp
@if ($conditional)
    {{ $third }}
@endif
@include("users")
@verbatim
    {{ $fourth }} @include("test")
@endverbatim
@php echo $fifth; @endphp';

    $expected = <<<'EXPECTED'
<?php echo e($first); ?>
<?php echo $second; ?>
<?php if($conditional): ?>
    <?php echo e($third); ?>
<?php endif; ?>
<?php echo $__env->make("users", \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    {{ $fourth }} @include("test")

<?php echo $fifth; ?>
EXPECTED;

    $result = $this->compiler->compileString($string);

    expect($result)->toBe($expected);
});

test('raw blocks dont get mixed up when some are removed by blade comments', function () {
    $string = '{{-- @verbatim Block #1 @endverbatim --}} @php "Block #2" @endphp';
    $expected = ' <?php "Block #2" ?>';

    expect($this->compiler->compileString($string))->toBe($expected);
});
