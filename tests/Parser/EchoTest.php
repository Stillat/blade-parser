<?php

namespace Stillat\BladeParser\Tests\Parser;

use Stillat\BladeParser\Nodes\EchoNode;
use Stillat\BladeParser\Tests\ParserTestCase;

class EchoTest extends ParserTestCase
{
    public function testEchoContainingStrings()
    {
        $echo = $this->getDocument('{{ "hello world" }}')->firstOfType(EchoNode::class)->asEcho();
        $this->assertSame(' "hello world" ', $echo->innerContent);

        $echo = $this->getDocument('{{{ "hello world" }}}')->firstOfType(EchoNode::class)->asEcho();
        $this->assertSame(' "hello world" ', $echo->innerContent);

        $echo = $this->getDocument('{!! "hello world" !!}')->firstOfType(EchoNode::class)->asEcho();
        $this->assertSame(' "hello world" ', $echo->innerContent);

        $echo = $this->getDocument("{{ 'hello world' }}")->firstOfType(EchoNode::class)->asEcho();
        $this->assertSame(" 'hello world' ", $echo->innerContent);

        $echo = $this->getDocument("{{ 'hello world' }}")->firstOfType(EchoNode::class)->asEcho();
        $this->assertSame(" 'hello world' ", $echo->innerContent);

        $echo = $this->getDocument("{{{ 'hello world' }}}")->firstOfType(EchoNode::class)->asEcho();
        $this->assertSame(" 'hello world' ", $echo->innerContent);

        $echo = $this->getDocument("{!! 'hello world' !!}")->firstOfType(EchoNode::class)->asEcho();
        $this->assertSame(" 'hello world' ", $echo->innerContent);
    }

    public function testEchoContainingStringsContainingStrings()
    {
        $echo = $this->getDocument('{{ "hello \"hello\" world" }}')->firstOfType(EchoNode::class)->asEcho();
        $this->assertSame(' "hello \"hello\" world" ', $echo->innerContent);

        $echo = $this->getDocument('{{{ "hello \"hello\" world" }}}')->firstOfType(EchoNode::class)->asEcho();
        $this->assertSame(' "hello \"hello\" world" ', $echo->innerContent);

        $echo = $this->getDocument('{!! "hello \"hello\" world" !!}')->firstOfType(EchoNode::class)->asEcho();
        $this->assertSame(' "hello \"hello\" world" ', $echo->innerContent);

        $echo = $this->getDocument("{{ 'hello \'hello\' world' }}")->firstOfType(EchoNode::class)->asEcho();
        $this->assertSame(" 'hello \'hello\' world' ", $echo->innerContent);

        $echo = $this->getDocument("{{ 'hello \'hello\' world' }}")->firstOfType(EchoNode::class)->asEcho();
        $this->assertSame(" 'hello \'hello\' world' ", $echo->innerContent);

        $echo = $this->getDocument("{{{ 'hello \'hello\' world' }}}")->firstOfType(EchoNode::class)->asEcho();
        $this->assertSame(" 'hello \'hello\' world' ", $echo->innerContent);

        $echo = $this->getDocument("{!! 'hello \'hello\' world' !!}")->firstOfType(EchoNode::class)->asEcho();
        $this->assertSame(" 'hello \'hello\' world' ", $echo->innerContent);
    }
}
