<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Compiler\CompilerServices\CoreDirectiveRetriever;

test('directive details retrieves information without exceptions', function () {
    $instance = CoreDirectiveRetriever::instance();
    expect($instance)->not->toBeNull();
    expect($instance)->toBeInstanceOf(CoreDirectiveRetriever::class);
    expect(CoreDirectiveRetriever::instance())->toEqual($instance);

    expect($instance->getIncludeDirectiveNames())->not->toBeEmpty();
    expect($instance->getDebugDirectiveNames())->not->toBeEmpty();
    expect($instance->getDirectivesRequiringOpen())->not->toBeEmpty();
    expect($instance->getDirectiveNames())->not->toBeEmpty();
    expect($instance->getNonStructureDirectiveNames())->not->toBeEmpty();
    $this->assertNotSame($instance->getNonStructureDirectiveNames(), $instance->getDirectiveNames());
    expect($instance->getDirectivesRequiringArguments())->not->toBeEmpty();
    expect($instance->getDirectivesThatMustNotHaveArguments())->not->toBeEmpty();
    expect($instance->getDirectivesWithOptionalArguments())->not->toBeEmpty();
});
