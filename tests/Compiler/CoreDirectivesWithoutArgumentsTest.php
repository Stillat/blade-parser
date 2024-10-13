<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);

test('core directives can be compiled without arguments and not throw null reference errors', function ($directive) {
    $this->expectNotToPerformAssertions();
    $this->compiler->compileString($directive);
})->with(\coreDirectives());
