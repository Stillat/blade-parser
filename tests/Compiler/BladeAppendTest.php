<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('append sections are compiled', function () {
    expect($this->compiler->compileString('@append'))->toBe('<?php $__env->appendSection(); ?>');
});
