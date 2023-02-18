<?php

namespace Stillat\BladeParser\Tests\Parser;

use Stillat\BladeParser\Errors\Exceptions\InvalidCastException;
use Stillat\BladeParser\Tests\ParserTestCase;

class NodeErrorsTest extends ParserTestCase
{
    public function testAsDirectiveThrowsInvalidCastException()
    {
        $this->expectException(InvalidCastException::class);
        $this->getDocument('{{ $hello }}')->getNodeArray()[0]->asDirective();
    }

    public function testAsLiteralThrowsInvalidCastException()
    {
        $this->expectException(InvalidCastException::class);
        $this->getDocument('{{ $hello }}')->getNodeArray()[0]->asLiteral();
    }

    public function testAsCommentThrowsInvalidCastException()
    {
        $this->expectException(InvalidCastException::class);
        $this->getDocument('{{ $hello }}')->getNodeArray()[0]->asComment();
    }

    public function testAsPhpBlockThrowsInvalidCastException()
    {
        $this->expectException(InvalidCastException::class);
        $this->getDocument('{{ $hello }}')->getNodeArray()[0]->asPhpBlock();
    }

    public function testAsVerbatimThrowsInvalidCastException()
    {
        $this->expectException(InvalidCastException::class);
        $this->getDocument('{{ $hello }}')->getNodeArray()[0]->asVerbatim();
    }

    public function testAsPhpTagThrowsInvalidCastException()
    {
        $this->expectException(InvalidCastException::class);
        $this->getDocument('{{ $hello }}')->getNodeArray()[0]->asPhpTag();
    }

    public function testAsEchoThrowsInvalidCastException()
    {
        $this->expectException(InvalidCastException::class);
        $this->getDocument('@lang("something")')->getNodeArray()[0]->asEcho();
    }
}
