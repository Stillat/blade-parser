<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('overwrite sections are compiled', function () {
    expect($this->compiler->compileString('@overwrite'))->toBe('<?php $__env->stopSection(true); ?>');
});
