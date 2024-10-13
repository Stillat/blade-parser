<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('stack is compiled', function () {
    $string = '@stack(\'foo\')';
    $expected = '<?php echo $__env->yieldPushContent(\'foo\'); ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});
