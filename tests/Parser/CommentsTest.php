<?php

namespace Stillat\BladeParser\Tests\Parser;

use Stillat\BladeParser\Nodes\CommentNode;
use Stillat\BladeParser\Tests\ParserTestCase;

class CommentsTest extends ParserTestCase
{
    public function testBasicComments()
    {
        $template = '{{-- This is a comment --}}';
        $nodes = $this->parseNodes($template);

        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(CommentNode::class, $nodes[0]);

        /** @var CommentNode $comment */
        $comment = $nodes[0];

        $this->assertSame('{{-- This is a comment --}}', $comment->content);
        $this->assertSame(' This is a comment ', $comment->innerContent);
    }

    public function testCommentsContainingThingsThatLookLikeBlade()
    {
        $template = <<<'EOT'
{{-- This is a comment with a @can inside --}}
EOT;

        $nodes = $this->parseNodes($template);

        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(CommentNode::class, $nodes[0]);

        /** @var CommentNode $comment */
        $comment = $nodes[0];

        $this->assertSame($template, $comment->content);
        $this->assertSame(' This is a comment with a @can inside ', $comment->innerContent);
    }

    public function testCommentsContainingCurlyBraces()
    {
        $template = <<<'EOT'
{{-- This is a comment {{ --}}
EOT;

        $nodes = $this->parseNodes($template);

        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(CommentNode::class, $nodes[0]);

        /** @var CommentNode $comment */
        $comment = $nodes[0];

        $this->assertSame($template, $comment->content);
        $this->assertSame(' This is a comment {{ ', $comment->innerContent);

        $template = <<<'EOT'
{{-- This is a comment -}} --}}
EOT;

        $nodes = $this->parseNodes($template);

        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(CommentNode::class, $nodes[0]);

        /** @var CommentNode $comment */
        $comment = $nodes[0];

        $this->assertSame($template, $comment->content);
        $this->assertSame(' This is a comment -}} ', $comment->innerContent);

        $template = <<<'EOT'
{{-- This is a {{ @can @verbatim comment -}} --}}
EOT;

        $nodes = $this->parseNodes($template);

        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(CommentNode::class, $nodes[0]);

        /** @var CommentNode $comment */
        $comment = $nodes[0];

        $this->assertSame($template, $comment->content);
        $this->assertSame(' This is a {{ @can @verbatim comment -}} ', $comment->innerContent);
    }

    public function testMultipleComments()
    {
        $template = <<<'EOT'
{{-- This is a comment --}}Literal{{-- This is another comment --}}
EOT;
        $nodes = $this->parseNodes($template);

        $this->assertCount(3, $nodes);
        $this->assertCommentContent($nodes[0], '{{-- This is a comment --}}');
        $this->assertLiteralContent($nodes[1], 'Literal');
        $this->assertCommentContent($nodes[2], '{{-- This is another comment --}}');
    }

    public function testCommentsWithBracesDoNotConfuseTheParser()
    {
        $template = <<<'EOT'
{{--a{{ $one }}b{{ $two }}c{{ $three }}d--}}a{{ $one }}b{{ $two }}c{{ $three }}d
EOT;

        $nodes = $this->parseNodes($template);

        $this->assertCount(8, $nodes);
        $this->assertCommentContent($nodes[0], '{{--a{{ $one }}b{{ $two }}c{{ $three }}d--}}');
        $this->assertLiteralContent($nodes[1], 'a');
        $this->assertEchoContent($nodes[2], '{{ $one }}');
        $this->assertLiteralContent($nodes[3], 'b');
        $this->assertEchoContent($nodes[4], '{{ $two }}');
        $this->assertLiteralContent($nodes[5], 'c');
        $this->assertEchoContent($nodes[6], '{{ $three }}');
        $this->assertLiteralContent($nodes[7], 'd');
    }

    public function testCommentWithoutSpaces()
    {
        $template = '{{--this is a comment--}}';
        $nodes = $this->parseNodes($template);
        $this->assertCount(1, $nodes);
        $this->assertCommentContent($nodes[0], $template);
    }

    public function testCommentsThatContainOpeningDirectives()
    {
        $template = '{{-- @foreach() --}}';
        $nodes = $this->parseNodes($template);
        $this->assertCount(1, $nodes);
        $this->assertCommentContent($nodes[0], $template);
    }
}
