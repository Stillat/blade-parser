<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('shows are compiled', function () {
    expect($this->compiler->compileString('@show'))->toBe('<?php echo $__env->yieldSection(); ?>');
});
