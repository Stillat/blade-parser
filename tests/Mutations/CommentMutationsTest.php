<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('comments can be changed', function () {
    $doc = $this->getDocument('One {{-- This is the comment --}} Two');
    $comment = $doc->getComments()->first();
    $comment->setContent('I am the content now!');

    expect((string) $doc)->toBe('One {{-- I am the content now! --}} Two');
});

test('comment whitespace can be overridden', function () {
    $template = <<<'EOT'
One {{--
                This is the comment
                        --}} Two
EOT;
    $doc = $this->getDocument($template);
    $comment = $doc->getComments()->first();
    $comment->setContent('I am the content now!', false);

    expect((string) $doc)->toBe('One {{-- I am the content now! --}} Two');
});

test('setting comments containing comments does not break things', function () {
    $doc = $this->getDocument('One {{-- This is the comment --}} Two');
    $comment = $doc->getComments()->first();
    $comment->setContent('{{--I am the content now!--}}');

    expect((string) $doc)->toBe('One {{-- I am the content now! --}} Two');
});
