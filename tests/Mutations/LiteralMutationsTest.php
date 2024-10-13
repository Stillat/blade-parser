<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Nodes\LiteralNode;

test('literal content can be changed', function () {
    $doc = $this->getDocument('a @if b @if c @if d');

    $doc->getLiterals()->each(function (LiteralNode $literal, $key) {
        // By default, the content will be trimmed and the original whitespace restored.
        $literal->setContent('     Literal: '.$key.'     ');
    });

    expect((string) $doc)->toBe('Literal: 0 @if Literal: 1 @if Literal: 2 @if Literal: 3');
});

test('original whitespace can be overridden', function () {
    $doc = $this->getDocument('a @if b @if c @if d');

    $doc->getLiterals()->each(function (LiteralNode $literal, $key) {
        $literal->setContent(' Literal: '.$key.' ', false);
    });

    expect((string) $doc)->toBe(' Literal: 0 @if Literal: 1 @if Literal: 2 @if Literal: 3 ');
});
