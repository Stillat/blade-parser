<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('unregistered directives are not parsed', function () {
    $template = '@_not_a_directive';
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(1);
    $this->assertLiteralContent($nodes[0], $template);

    $this->registerDirective('_not_a_directive');
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(1);
    $this->assertDirectiveName($nodes[0], '_not_a_directive');
});
