<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Nodes\EchoNode;

test('echo containing strings', function () {
    $echo = $this->getDocument('{{ "hello world" }}')->firstOfType(EchoNode::class)->asEcho();
    expect($echo->innerContent)->toBe(' "hello world" ');

    $echo = $this->getDocument('{{{ "hello world" }}}')->firstOfType(EchoNode::class)->asEcho();
    expect($echo->innerContent)->toBe(' "hello world" ');

    $echo = $this->getDocument('{!! "hello world" !!}')->firstOfType(EchoNode::class)->asEcho();
    expect($echo->innerContent)->toBe(' "hello world" ');

    $echo = $this->getDocument("{{ 'hello world' }}")->firstOfType(EchoNode::class)->asEcho();
    expect($echo->innerContent)->toBe(" 'hello world' ");

    $echo = $this->getDocument("{{ 'hello world' }}")->firstOfType(EchoNode::class)->asEcho();
    expect($echo->innerContent)->toBe(" 'hello world' ");

    $echo = $this->getDocument("{{{ 'hello world' }}}")->firstOfType(EchoNode::class)->asEcho();
    expect($echo->innerContent)->toBe(" 'hello world' ");

    $echo = $this->getDocument("{!! 'hello world' !!}")->firstOfType(EchoNode::class)->asEcho();
    expect($echo->innerContent)->toBe(" 'hello world' ");
});

test('echo containing strings containing strings', function () {
    $echo = $this->getDocument('{{ "hello \"hello\" world" }}')->firstOfType(EchoNode::class)->asEcho();
    expect($echo->innerContent)->toBe(' "hello \"hello\" world" ');

    $echo = $this->getDocument('{{{ "hello \"hello\" world" }}}')->firstOfType(EchoNode::class)->asEcho();
    expect($echo->innerContent)->toBe(' "hello \"hello\" world" ');

    $echo = $this->getDocument('{!! "hello \"hello\" world" !!}')->firstOfType(EchoNode::class)->asEcho();
    expect($echo->innerContent)->toBe(' "hello \"hello\" world" ');

    $echo = $this->getDocument("{{ 'hello \'hello\' world' }}")->firstOfType(EchoNode::class)->asEcho();
    expect($echo->innerContent)->toBe(" 'hello \'hello\' world' ");

    $echo = $this->getDocument("{{ 'hello \'hello\' world' }}")->firstOfType(EchoNode::class)->asEcho();
    expect($echo->innerContent)->toBe(" 'hello \'hello\' world' ");

    $echo = $this->getDocument("{{{ 'hello \'hello\' world' }}}")->firstOfType(EchoNode::class)->asEcho();
    expect($echo->innerContent)->toBe(" 'hello \'hello\' world' ");

    $echo = $this->getDocument("{!! 'hello \'hello\' world' !!}")->firstOfType(EchoNode::class)->asEcho();
    expect($echo->innerContent)->toBe(" 'hello \'hello\' world' ");
});
