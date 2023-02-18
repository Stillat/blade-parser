<?php

namespace Stillat\BladeParser\Tests\Structures;

use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\EchoNode;
use Stillat\BladeParser\Nodes\LiteralNode;
use Stillat\BladeParser\Nodes\Structures\ForElse;
use Stillat\BladeParser\Tests\ParserTestCase;

class ForElseStructureTest extends ParserTestCase
{
    public function testForElseStructures()
    {
        $template = <<<'EOT'
@forelse ($users as $user)
    <li>{{ $user->name }}</li>
@empty
    <p>No users</p>
@endforelse
EOT;
        $doc = $this->getDocument($template);
        $doc->resolveStructures();

        $forElse = $doc->findDirectiveByName('forelse');
        $this->assertNotNull($forElse);
        $this->assertNotNull($forElse->structure);
        $this->assertTrue($forElse->isStructure);

        $this->assertInstanceOf(ForElse::class, $forElse->structure);

        /** @var ForElse $structure */
        $structure = $forElse->structure;
        $this->assertTrue($structure->hasEmptyClause());
        $this->assertTrue($structure->isValid());
        $this->assertCount(3, $structure->getLoopBody());
        $this->assertCount(1, $structure->getEmptyBody());

        $this->assertSame($structure->constructedFrom, $forElse);

        $loopBody = $structure->getLoopBody();
        $this->assertInstanceOf(LiteralNode::class, $loopBody[0]);
        $this->assertInstanceOf(EchoNode::class, $loopBody[1]);
        $this->assertInstanceOf(LiteralNode::class, $loopBody[2]);

        $emptyBody = $structure->getEmptyBody();
        $this->assertInstanceOf(LiteralNode::class, $emptyBody[0]);
        $this->assertStringContainsString('No users<', $emptyBody[0]->content);
    }

    public function testNestedForElseStructures()
    {
        $template = <<<'EOT'
@forelse ($users as $user)
    <li>{{ $user->name }}</li>
    
    @forelse ($users2 as $user2)
        <li>{{ $user2->name }}</li>
    @empty
        <p>No users2</p>
    @endforelse
    
@empty
    <p>No users</p>
@endforelse
EOT;
        $doc = $this->getDocument($template);
        $doc->resolveStructures();

        $forElseStatements = $doc->findDirectivesByName('forelse');
        $this->assertCount(2, $forElseStatements);

        /** @var ForElse $forElse */
        $forElse = $forElseStatements[0];

        /** @var ForElse $structure */
        $structure = $forElse->structure;
        $this->assertTrue($structure->hasEmptyClause());
        $this->assertCount(5, $structure->getLoopBody());
        $this->assertCount(1, $structure->getEmptyBody());

        $this->assertSame($structure->constructedFrom, $forElse);

        $loopBody = $structure->getLoopBody();
        $this->assertInstanceOf(LiteralNode::class, $loopBody[0]);
        $this->assertInstanceOf(EchoNode::class, $loopBody[1]);
        $this->assertInstanceOf(LiteralNode::class, $loopBody[2]);
        $this->assertInstanceOf(DirectiveNode::class, $loopBody[3]);
        $this->assertInstanceOf(LiteralNode::class, $loopBody[4]);

        $emptyBody = $structure->getEmptyBody();
        $this->assertInstanceOf(LiteralNode::class, $emptyBody[0]);
        $this->assertStringContainsString('No users<', $emptyBody[0]->content);

        $forElse = $forElseStatements[1];

        /** @var ForElse $structure */
        $structure = $forElse->structure;
        $this->assertTrue($structure->hasEmptyClause());
        $this->assertCount(3, $structure->getLoopBody());
        $this->assertCount(1, $structure->getEmptyBody());

        $this->assertSame($structure->constructedFrom, $forElse);

        $loopBody = $structure->getLoopBody();
        $this->assertInstanceOf(LiteralNode::class, $loopBody[0]);
        $this->assertInstanceOf(EchoNode::class, $loopBody[1]);
        $this->assertInstanceOf(LiteralNode::class, $loopBody[2]);

        $emptyBody = $structure->getEmptyBody();
        $this->assertInstanceOf(LiteralNode::class, $emptyBody[0]);
        $this->assertStringContainsString('No users2<', $emptyBody[0]->content);
    }

    public function testForElseWithoutEmpty()
    {
        $template = <<<'EOT'
@forelse ($users as $user)
    <li>{{ $user->name }}</li>
@endforelse
EOT;
        $doc = $this->getDocument($template);
        $doc->resolveStructures();

        $forElse = $doc->findDirectiveByName('forelse');
        $this->assertNotNull($forElse);
        $this->assertTrue($forElse->isStructure);
        $this->assertNotNull($forElse->structure);

        /** @var ForElse $structure */
        $structure = $forElse->structure;
        $this->assertFalse($structure->hasEmptyClause());
        $this->assertCount(3, $structure->loopBody);
        $this->assertCount(0, $structure->emptyBody);
        $this->assertFalse($structure->isValid());

        $this->assertInstanceOf(LiteralNode::class, $structure->loopBody[0]);
        $this->assertInstanceOf(EchoNode::class, $structure->loopBody[1]);
        $this->assertInstanceOf(LiteralNode::class, $structure->loopBody[2]);
    }

    public function testForElseWithNestedForElseWithoutEmpty()
    {
        $template = <<<'EOT'
@forelse ($users as $user)
    <li>{{ $user->name }}</li>
    
    @forelse ($users as $user)
        <li>{{ $user->name }}</li>
    @endforelse
    
@empty
    <p>No users</p>
@endforelse
EOT;
        $doc = $this->getDocument($template);
        $doc->resolveStructures();

        $forElseStatements = $doc->findDirectivesByName('forelse');
        $this->assertCount(2, $forElseStatements);

        $forElse = $forElseStatements[0];

        /** @var ForElse $structure */
        $structure = $forElse->structure;
        $this->assertTrue($structure->hasEmptyClause());
        $this->assertCount(5, $structure->getLoopBody());
        $this->assertCount(1, $structure->getEmptyBody());

        $this->assertSame($structure->constructedFrom, $forElse);

        $loopBody = $structure->getLoopBody();
        $this->assertInstanceOf(LiteralNode::class, $loopBody[0]);
        $this->assertInstanceOf(EchoNode::class, $loopBody[1]);
        $this->assertInstanceOf(LiteralNode::class, $loopBody[2]);
        $this->assertInstanceOf(DirectiveNode::class, $loopBody[3]);
        $this->assertInstanceOf(LiteralNode::class, $loopBody[4]);

        $emptyBody = $structure->getEmptyBody();
        $this->assertInstanceOf(LiteralNode::class, $emptyBody[0]);
        $this->assertStringContainsString('No users<', $emptyBody[0]->content);

        $forElse = $forElseStatements[1];

        $this->assertNotNull($forElse);
        $this->assertTrue($forElse->isStructure);
        $this->assertNotNull($forElse->structure);

        /** @var ForElse $structure */
        $structure = $forElse->structure;
        $this->assertFalse($structure->hasEmptyClause());
        $this->assertCount(3, $structure->loopBody);
        $this->assertCount(0, $structure->emptyBody);

        $this->assertInstanceOf(LiteralNode::class, $structure->loopBody[0]);
        $this->assertInstanceOf(EchoNode::class, $structure->loopBody[1]);
        $this->assertInstanceOf(LiteralNode::class, $structure->loopBody[2]);
    }

    public function testForElseWithMultipleEmptyDirectives()
    {
        $template = <<<'EOT'
One
@forelse ($users as $user)
    Loop Body
@empty
    Empty One
@empty
    Empty Two
@endforelse
Two
EOT;
        $doc = $this->getDocument($template);
        $doc->resolveStructures();

        $directive = $doc->findDirectiveByName('forelse');
        $forElse = $directive->getForElse();

        $emptyBody = $forElse->emptyBody;
        $this->assertFalse($forElse->isValid());
        $this->assertNotNull($forElse->emptyDirective);
        $this->assertCount(3, $emptyBody);
        $this->assertInstanceOf(LiteralNode::class, $emptyBody[0]);
        $this->assertStringContainsString('Empty One', $emptyBody[0]->content);
        $this->assertInstanceOf(DirectiveNode::class, $emptyBody[1]);
        $this->assertDirectiveContent($emptyBody[1], 'empty');
        $this->assertInstanceOf(LiteralNode::class, $emptyBody[2]);
        $this->assertStringContainsString('Empty Two', $emptyBody[2]->content);
        $this->assertNotEquals($forElse->emptyDirective, $emptyBody[2]);
        $this->assertSame(2, $forElse->getEmptyDirectiveCount());
    }
}
