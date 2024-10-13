<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Nodes\LiteralNode;

beforeEach(function () {
    $this->workspace = $this->getWorkspace('two');
    $this->workspace->resolveStructures();
});

test('find directives by name', function () {
    expect($this->workspace->findDirectivesByName('include'))->toHaveCount(2);
});

test('get comments', function () {
    expect($this->workspace->getComments())->toHaveCount(4);
});

test('has any comments', function () {
    expect($this->workspace->hasAnyComments())->toBeTrue();
});

test('get echoes', function () {
    expect($this->workspace->getEchoes())->toHaveCount(1);
});

test('get php blocks', function () {
    expect($this->workspace->getPhpBlocks())->toHaveCount(2);
});

test('get php tags', function () {
    expect($this->workspace->getPhpTags())->toHaveCount(2);
});

test('get verbatim blocks', function () {
    expect($this->workspace->getVerbatimBlocks())->toHaveCount(2);
});

test('get literals', function () {
    expect($this->workspace->getLiterals())->toHaveCount(55);
});

test('get directives', function () {
    expect($this->workspace->getDirectives())->toHaveCount(42);
});

test('get has any directives', function () {
    expect($this->workspace->hasAnyDirectives())->toBeTrue();
});

function getGetComponents()
{
    expect($this->workspace->getComponents())->toHaveCount(2);
}

test('get opening component tags', function () {
    expect($this->workspace->getOpeningComponentTags())->toHaveCount(2);
});

test('find components by tag name', function () {
    expect($this->workspace->findComponentsByTagName('profile'))->toHaveCount(2);
    expect($this->workspace->findComponentsByTagName('alert'))->toHaveCount(0);
});

test('has any components', function () {
    expect($this->workspace->hasAnyComponents())->toBeTrue();
});

test('has directive', function () {
    expect($this->workspace->hasDirective('include'))->toBeTrue();
});

test('all of type', function () {
    expect($this->workspace->allOfType(LiteralNode::class))->toHaveCount(55);
});

test('all not of type', function () {
    expect($this->workspace->allNotOfType(LiteralNode::class))->toHaveCount(55);
});

test('get all structures', function () {
    expect($this->workspace->getAllStructures())->toHaveCount(18);
});

test('get root structures', function () {
    expect($this->workspace->getRootStructures())->toHaveCount(6);
});

test('get all switch statements', function () {
    expect($this->workspace->getAllSwitchStatements())->toHaveCount(4);
});

test('get root switch statements', function () {
    expect($this->workspace->getRootSwitchStatements())->toHaveCount(2);
});

test('get all conditions', function () {
    expect($this->workspace->getAllConditions())->toHaveCount(4);
});

test('get root conditions', function () {
    expect($this->workspace->getRootConditions())->toHaveCount(2);
});

test('get all for else', function () {
    expect($this->workspace->getAllForElse())->toHaveCount(4);
});

test('get root for else', function () {
    expect($this->workspace->getRootForElse())->toHaveCount(2);
});
