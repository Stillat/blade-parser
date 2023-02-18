<?php

namespace Stillat\BladeParser\Tests\Mutations;

use Stillat\BladeParser\Tests\ParserTestCase;

class CommentMutationsTest extends ParserTestCase
{
    public function testCommentsCanBeChanged()
    {
        $doc = $this->getDocument('One {{-- This is the comment --}} Two');
        $comment = $doc->getComments()->first();
        $comment->setContent('I am the content now!');

        $this->assertSame('One {{-- I am the content now! --}} Two', (string) $doc);
    }

    public function testCommentWhitespaceCanBeOverridden()
    {
        $template = <<<'EOT'
One {{--
                This is the comment
                        --}} Two
EOT;
        $doc = $this->getDocument($template);
        $comment = $doc->getComments()->first();
        $comment->setContent('I am the content now!', false);

        $this->assertSame('One {{-- I am the content now! --}} Two', (string) $doc);
    }

    public function testSettingCommentsContainingCommentsDoesNotBreakThings()
    {
        $doc = $this->getDocument('One {{-- This is the comment --}} Two');
        $comment = $doc->getComments()->first();
        $comment->setContent('{{--I am the content now!--}}');

        $this->assertSame('One {{-- I am the content now! --}} Two', (string) $doc);
    }
}
