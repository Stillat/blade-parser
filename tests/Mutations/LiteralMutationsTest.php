<?php

namespace Stillat\BladeParser\Tests\Mutations;

use Stillat\BladeParser\Nodes\LiteralNode;
use Stillat\BladeParser\Tests\ParserTestCase;

class LiteralMutationsTest extends ParserTestCase
{
    public function testLiteralContentCanBeChanged()
    {
        $doc = $this->getDocument('a @if b @if c @if d');

        $doc->getLiterals()->each(function (LiteralNode $literal, $key) {
            // By default, the content will be trimmed and the original whitespace restored.
            $literal->setContent('     Literal: '.$key.'     ');
        });

        $this->assertSame('Literal: 0 @if Literal: 1 @if Literal: 2 @if Literal: 3', (string) $doc);
    }

    public function testOriginalWhitespaceCanBeOverridden()
    {
        $doc = $this->getDocument('a @if b @if c @if d');

        $doc->getLiterals()->each(function (LiteralNode $literal, $key) {
            $literal->setContent(' Literal: '.$key.' ', false);
        });

        $this->assertSame(' Literal: 0 @if Literal: 1 @if Literal: 2 @if Literal: 3 ', (string) $doc);
    }
}
