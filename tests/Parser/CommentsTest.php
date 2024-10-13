<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Nodes\CommentNode;

test('basic comments', function () {
    $template = '{{-- This is a comment --}}';
    $nodes = $this->parseNodes($template);

    expect($nodes)->toHaveCount(1);
    expect($nodes[0])->toBeInstanceOf(CommentNode::class);

    /** @var CommentNode $comment */
    $comment = $nodes[0];

    expect($comment->content)->toBe('{{-- This is a comment --}}');
    expect($comment->innerContent)->toBe(' This is a comment ');
});

test('comments containing things that look like blade', function () {
    $template = <<<'EOT'
{{-- This is a comment with a @can inside --}}
EOT;

    $nodes = $this->parseNodes($template);

    expect($nodes)->toHaveCount(1);
    expect($nodes[0])->toBeInstanceOf(CommentNode::class);

    /** @var CommentNode $comment */
    $comment = $nodes[0];

    expect($comment->content)->toBe($template);
    expect($comment->innerContent)->toBe(' This is a comment with a @can inside ');
});

test('comments containing curly braces', function () {
    $template = <<<'EOT'
{{-- This is a comment {{ --}}
EOT;

    $nodes = $this->parseNodes($template);

    expect($nodes)->toHaveCount(1);
    expect($nodes[0])->toBeInstanceOf(CommentNode::class);

    /** @var CommentNode $comment */
    $comment = $nodes[0];

    expect($comment->content)->toBe($template);
    expect($comment->innerContent)->toBe(' This is a comment {{ ');

    $template = <<<'EOT'
{{-- This is a comment -}} --}}
EOT;

    $nodes = $this->parseNodes($template);

    expect($nodes)->toHaveCount(1);
    expect($nodes[0])->toBeInstanceOf(CommentNode::class);

    /** @var CommentNode $comment */
    $comment = $nodes[0];

    expect($comment->content)->toBe($template);
    expect($comment->innerContent)->toBe(' This is a comment -}} ');

    $template = <<<'EOT'
{{-- This is a {{ @can @verbatim comment -}} --}}
EOT;

    $nodes = $this->parseNodes($template);

    expect($nodes)->toHaveCount(1);
    expect($nodes[0])->toBeInstanceOf(CommentNode::class);

    /** @var CommentNode $comment */
    $comment = $nodes[0];

    expect($comment->content)->toBe($template);
    expect($comment->innerContent)->toBe(' This is a {{ @can @verbatim comment -}} ');
});

test('multiple comments', function () {
    $template = <<<'EOT'
{{-- This is a comment --}}Literal{{-- This is another comment --}}
EOT;
    $nodes = $this->parseNodes($template);

    expect($nodes)->toHaveCount(3);
    $this->assertCommentContent($nodes[0], '{{-- This is a comment --}}');
    $this->assertLiteralContent($nodes[1], 'Literal');
    $this->assertCommentContent($nodes[2], '{{-- This is another comment --}}');
});

test('comments with braces do not confuse the parser', function () {
    $template = <<<'EOT'
{{--a{{ $one }}b{{ $two }}c{{ $three }}d--}}a{{ $one }}b{{ $two }}c{{ $three }}d
EOT;

    $nodes = $this->parseNodes($template);

    expect($nodes)->toHaveCount(8);
    $this->assertCommentContent($nodes[0], '{{--a{{ $one }}b{{ $two }}c{{ $three }}d--}}');
    $this->assertLiteralContent($nodes[1], 'a');
    $this->assertEchoContent($nodes[2], '{{ $one }}');
    $this->assertLiteralContent($nodes[3], 'b');
    $this->assertEchoContent($nodes[4], '{{ $two }}');
    $this->assertLiteralContent($nodes[5], 'c');
    $this->assertEchoContent($nodes[6], '{{ $three }}');
    $this->assertLiteralContent($nodes[7], 'd');
});

test('comment without spaces', function () {
    $template = '{{--this is a comment--}}';
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(1);
    $this->assertCommentContent($nodes[0], $template);
});

test('comments that contain opening directives', function () {
    $template = '{{-- @foreach() --}}';
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(1);
    $this->assertCommentContent($nodes[0], $template);
});
