<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('stop sections are compiled', function () {
    expect($this->compiler->compileString('@stop'))->toBe('<?php $__env->stopSection(); ?>');
});
