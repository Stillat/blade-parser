<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('statement is compiled with safe default encoding options', function () {
    $string = 'var foo = @json($var);';
    $expected = 'var foo = <?php echo json_encode($var, 15, 512) ?>;';

    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('encoding options can be overwritten', function () {
    $string = 'var foo = @json($var, JSON_HEX_TAG);';
    $expected = 'var foo = <?php echo json_encode($var, JSON_HEX_TAG, 512) ?>;';

    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('complex json expressions can be compiled', function () {
    $string = 'var foo = @json(DB::query()->selectRaw("1, CONCAT(2, \' \', 3) AS name")->get())';
    $expected = <<<'EXPECTED'
var foo = <?php echo json_encode(DB::query()->selectRaw("1, CONCAT(2, ' ', 3) AS name")->get(), 15, 512) ?>
EXPECTED;

    expect($this->compiler->compileString($string))->toEqual($expected);
});
