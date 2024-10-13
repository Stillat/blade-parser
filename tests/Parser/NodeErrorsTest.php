<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Errors\Exceptions\InvalidCastException;

test('as directive throws invalid cast exception', function () {
    $this->expectException(InvalidCastException::class);
    $this->getDocument('{{ $hello }}')->getNodeArray()[0]->asDirective();
});

test('as literal throws invalid cast exception', function () {
    $this->expectException(InvalidCastException::class);
    $this->getDocument('{{ $hello }}')->getNodeArray()[0]->asLiteral();
});

test('as comment throws invalid cast exception', function () {
    $this->expectException(InvalidCastException::class);
    $this->getDocument('{{ $hello }}')->getNodeArray()[0]->asComment();
});

test('as php block throws invalid cast exception', function () {
    $this->expectException(InvalidCastException::class);
    $this->getDocument('{{ $hello }}')->getNodeArray()[0]->asPhpBlock();
});

test('as verbatim throws invalid cast exception', function () {
    $this->expectException(InvalidCastException::class);
    $this->getDocument('{{ $hello }}')->getNodeArray()[0]->asVerbatim();
});

test('as php tag throws invalid cast exception', function () {
    $this->expectException(InvalidCastException::class);
    $this->getDocument('{{ $hello }}')->getNodeArray()[0]->asPhpTag();
});

test('as echo throws invalid cast exception', function () {
    $this->expectException(InvalidCastException::class);
    $this->getDocument('@lang("something")')->getNodeArray()[0]->asEcho();
});
