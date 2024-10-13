<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Nodes\DirectiveNode;

test('directive names can be changed', function () {
    $doc = $this->getDocument('One @can Two');
    $directive = $doc->getDirectives()->first();
    $directive->setName('auth');

    expect((string) $doc)->toBe('One @auth Two');
});

test('arguments can be removed', function () {
    $doc = $this->getDocument('One @if ($this == that) Two');

    /** @var DirectiveNode $directive */
    $directive = $doc->getDirectives()->first();
    $directive->setName('auth');
    $directive->removeArguments();

    expect((string) $doc)->toBe('One @auth Two');
});

test('directive arguments can be changed', function () {
    $doc = $this->getDocument('One @if ($this == $that) Two');
    $directive = $doc->getDirectives()->first();

    $directive->setName('unless');
    $directive->setArguments('$that == $this');

    expect((string) $doc)->toBe('One @unless ($that == $this) Two');
});

test('passing parentheses does not double up', function () {
    $doc = $this->getDocument('One @if ($this == $that) Two');
    $directive = $doc->getDirectives()->first();

    $directive->setName('unless');
    $directive->setArguments('($that == $this)');

    expect((string) $doc)->toBe('One @unless ($that == $this) Two');

    $directive->setName('unless');
    $directive->setArguments('((((((($that == $this)))))))');

    expect((string) $doc)->toBe('One @unless ($that == $this) Two');
});

test('arguments can be added', function () {
    $doc = $this->getDocument(' @lang ');
    $directive = $doc->findDirectiveByName('lang');
    expect($directive->arguments)->toBeNull();
    $directive->setArguments('"something"');
    expect($directive->isDirty())->toBeTrue();
    expect($directive->arguments)->not->toBeNull();

    expect($directive->toString())->toBe('@lang ("something")');
});
