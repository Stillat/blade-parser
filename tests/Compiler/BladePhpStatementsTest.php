<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('php statements with expression are compiled', function () {
    $string = '@php($set = true)';
    $expected = '<?php ($set = true); ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('string with parenthesis with end php', function () {
    $string = "@php(\$data = ['related_to' => 'issue#45388'];) {{ \$data }} @endphp";
    $expected = <<<'EXPECTED'
<?php ($data = ['related_to' => 'issue#45388'];); ?> <?php echo e($data); ?> 
EXPECTED;

    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('php statements without expression are ignored', function () {
    $string = '@php';
    $expected = '@php';
    expect($this->compiler->compileString($string))->toEqual($expected);

    $string = '{{ "Ignore: @php" }}';
    $expected = '<?php echo e("Ignore: @php"); ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('php statements dont parse blade code', function () {
    $string = '@php echo "{{ This is a blade tag }}" @endphp';
    $expected = '<?php echo "{{ This is a blade tag }}" ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('verbatim and php statements dont get mixed up', function () {
    $string = "@verbatim {{ Hello, I'm not blade! }}"
        ."\n@php echo 'And I'm not PHP!' @endphp"
        ."\n@endverbatim {{ 'I am Blade' }}"
        ."\n@php echo 'I am PHP {{ not Blade }}' @endphp";

    $expected = <<<'EXPECTED'
 {{ Hello, I'm not blade! }}
@php echo 'And I'm not PHP!' @endphp
 <?php echo e('I am Blade'); ?>
<?php echo 'I am PHP {{ not Blade }}' ?>
EXPECTED;

    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('string with parenthesis cannot be compiled', function () {
    $string = "@php(\$data = ['test' => ')'])";

    $expected = "<?php (\$data = ['test' => ')']); ?>";

    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('string with empty string data value', function () {
    $string = "@php(\$data = ['test' => ''])";

    $expected = "<?php (\$data = ['test' => '']); ?>";

    expect($this->compiler->compileString($string))->toEqual($expected);

    $string = "@php(\$data = ['test' => \"\"])";

    $expected = "<?php (\$data = ['test' => \"\"]); ?>";

    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('string with escaping data value', function () {
    $string = "@php(\$data = ['test' => 'won\\'t break'])";

    $expected = "<?php (\$data = ['test' => 'won\\'t break']); ?>";

    expect($this->compiler->compileString($string))->toEqual($expected);

    $string = "@php(\$data = ['test' => \"\\\"escaped\\\"\"])";

    $expected = "<?php (\$data = ['test' => \"\\\"escaped\\\"\"]); ?>";

    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('compilation of mixed php statements', function () {
    $string = '@php($set = true) @php ($hello = \'hi\') @php echo "Hello world" @endphp';
    $expected = '<?php ($set = true); ?> <?php ($hello = \'hi\'); ?> <?php echo "Hello world" ?>';

    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('compilation of mixed usage statements', function () {
    $string = <<<'BLADE'
@php (
        $classes = [
            'admin-font-mono', // Font weight
        ])
    @endphp
BLADE;

    $expected = <<<'EXPECTED'
<?php (
        $classes = [
            'admin-font-mono', // Font weight
        ]); ?>
    
EXPECTED;

    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('multiline php statements with parentheses can be compiled', function () {
    $string = '@php ('
        ."\n    \$classes = ["
        ."\n        'admin-font-mono'"
        ."\n    ])"
        ."\n@endphp"
        ."\n"
        ."\n<span class=\"{{ implode(' ', \$classes) }}\">Laravel</span>";

    $expected = <<<'EXPECTED'
<?php (
    $classes = [
        'admin-font-mono'
    ]); ?>


<span class="<?php echo e(implode(' ', $classes)); ?>">Laravel</span>
EXPECTED;

    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('mixed of php statements can be compiled', function () {
    $string = '@php($total = 0)'
        ."\n{{-- ... --}}"
        ."\n<div>{{ \$total }}</div>"
        ."\n@php"
        ."\n    // ..."
        ."\n@endphp";

    $expected = <<<'EXPECTED'
<?php ($total = 0); ?>

<div><?php echo e($total); ?></div>
<?php // ... ?>
EXPECTED;

    expect($this->compiler->compileString($string))->toEqual($expected);
});
