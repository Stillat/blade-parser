<?php

namespace Stillat\BladeParser\Tests\Mutations;

use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Tests\ParserTestCase;

class DirectiveMutationsTest extends ParserTestCase
{
    public function testDirectiveNamesCanBeChanged()
    {
        $doc = $this->getDocument('One @can Two');
        $directive = $doc->getDirectives()->first();
        $directive->setName('auth');

        $this->assertSame('One @auth Two', (string) $doc);
    }

    public function testArgumentsCanBeRemoved()
    {
        $doc = $this->getDocument('One @if ($this == that) Two');
        /** @var DirectiveNode $directive */
        $directive = $doc->getDirectives()->first();
        $directive->setName('auth');
        $directive->removeArguments();

        $this->assertSame('One @auth Two', (string) $doc);
    }

    public function testDirectiveArgumentsCanBeChanged()
    {
        $doc = $this->getDocument('One @if ($this == $that) Two');
        $directive = $doc->getDirectives()->first();

        $directive->setName('unless');
        $directive->setArguments('$that == $this');

        $this->assertSame('One @unless ($that == $this) Two', (string) $doc);
    }

    public function testPassingParenthesesDoesNotDoubleUp()
    {
        $doc = $this->getDocument('One @if ($this == $that) Two');
        $directive = $doc->getDirectives()->first();

        $directive->setName('unless');
        $directive->setArguments('($that == $this)');

        $this->assertSame('One @unless ($that == $this) Two', (string) $doc);

        $directive->setName('unless');
        $directive->setArguments('((((((($that == $this)))))))');

        $this->assertSame('One @unless ($that == $this) Two', (string) $doc);
    }

    public function testArgumentsCanBeAdded()
    {
        $doc = $this->getDocument(' @lang ');
        $directive = $doc->findDirectiveByName('lang');
        $this->assertNull($directive->arguments);
        $directive->setArguments('"something"');
        $this->assertTrue($directive->isDirty());
        $this->assertNotNull($directive->arguments);

        $this->assertSame('@lang ("something")', $directive->toString());
    }
}
