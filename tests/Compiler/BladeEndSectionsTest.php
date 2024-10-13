<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('end sections are compiled', function () {
    expect($this->compiler->compileString('@endsection'))->toBe('<?php $__env->stopSection(); ?>');
});
