<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('get set echo handlers', function () {
    $handlers = [
        'test',
    ];

    expect($this->compiler->getEchoHandlers())->toHaveCount(0);
    $this->compiler->setEchoHandlers($handlers);
    expect($this->compiler->getEchoHandlers())->toHaveCount(1);

    expect($this->compiler->getEchoHandlers())->toBe($handlers);
});

test('get set parser strictness', function () {
    expect($this->compiler->getParserErrorsIsStrict())->toBeFalse();
    $this->compiler->setParserErrorsIsStrict(true);
    expect($this->compiler->getParserErrorsIsStrict())->toBeTrue();
});

test('get set compiles component tags', function () {
    expect($this->compiler->getCompilesComponentTags())->toBeTrue();
    $this->compiler->setCompilesComponentTags(false);
    expect($this->compiler->getCompilesComponentTags())->toBeFalse();
});

test('get set conditions', function () {
    $conditions = [
        'cond',
    ];

    expect($this->compiler->getConditions())->toHaveCount(0);
    $this->compiler->setConditions($conditions);
    expect($this->compiler->getConditions())->toHaveCount(1);

    expect($this->compiler->getConditions())->toBe($conditions);
});

test('get set precompilers', function () {
    expect($this->compiler->getPrecompilers())->toHaveCount(0);
    $this->compiler->precompiler(fn ($s) => $s);
    expect($this->compiler->getPrecompilers())->toHaveCount(1);
});
