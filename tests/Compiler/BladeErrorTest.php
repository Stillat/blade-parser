<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('php statements with expression are compiled', function () {
    $string = '@php($set = true)';
    $expected = '<?php ($set = true); ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('string with parenthesis with end php', function () {
    $string = "@php(\$data = ['related_to' => 'issue#45388'];) {{ \$data }} @endphp";
    $laravelExpected = "<?php(\$data = ['related_to' => 'issue#45388'];) {{ \$data }} ?>";

    // Note: The expected output has been changed to ensure that the
    // @endphp does not appear in the output. At the moment
    // @php followed by arguments does not begin a blocked region.
    // The PHP contents are output, as well as the intended echo.
    // The @endphp compiler simply outputs an empty string.
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

    // Note: In the original Laravel test for this
    // is what is asserted to be the same. Handling
    // strings with parenthesis is supported by
    // this parser, so the test has been updated
    // to reflect that difference in parsing.
    $actual = "<?php (\$data = ['test' => '); ?>'])";

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
