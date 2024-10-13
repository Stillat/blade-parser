<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\EchoNode;
use Stillat\BladeParser\Nodes\LiteralNode;
use Stillat\BladeParser\Nodes\Structures\ForElse;

test('for else structures', function () {
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
    expect($forElse)->not->toBeNull();
    expect($forElse->structure)->not->toBeNull();
    expect($forElse->isStructure)->toBeTrue();

    expect($forElse->structure)->toBeInstanceOf(ForElse::class);

    /** @var ForElse $structure */
    $structure = $forElse->structure;
    expect($structure->hasEmptyClause())->toBeTrue();
    expect($structure->isValid())->toBeTrue();
    expect($structure->getLoopBody())->toHaveCount(3);
    expect($structure->getEmptyBody())->toHaveCount(1);

    expect($forElse)->toBe($structure->constructedFrom);

    $loopBody = $structure->getLoopBody();
    expect($loopBody[0])->toBeInstanceOf(LiteralNode::class);
    expect($loopBody[1])->toBeInstanceOf(EchoNode::class);
    expect($loopBody[2])->toBeInstanceOf(LiteralNode::class);

    $emptyBody = $structure->getEmptyBody();
    expect($emptyBody[0])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('No users<', $emptyBody[0]->content);
});

test('nested for else structures', function () {
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
    expect($forElseStatements)->toHaveCount(2);

    /** @var ForElse $forElse */
    $forElse = $forElseStatements[0];

    /** @var ForElse $structure */
    $structure = $forElse->structure;
    expect($structure->hasEmptyClause())->toBeTrue();
    expect($structure->getLoopBody())->toHaveCount(5);
    expect($structure->getEmptyBody())->toHaveCount(1);

    expect($forElse)->toBe($structure->constructedFrom);

    $loopBody = $structure->getLoopBody();
    expect($loopBody[0])->toBeInstanceOf(LiteralNode::class);
    expect($loopBody[1])->toBeInstanceOf(EchoNode::class);
    expect($loopBody[2])->toBeInstanceOf(LiteralNode::class);
    expect($loopBody[3])->toBeInstanceOf(DirectiveNode::class);
    expect($loopBody[4])->toBeInstanceOf(LiteralNode::class);

    $emptyBody = $structure->getEmptyBody();
    expect($emptyBody[0])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('No users<', $emptyBody[0]->content);

    $forElse = $forElseStatements[1];

    /** @var ForElse $structure */
    $structure = $forElse->structure;
    expect($structure->hasEmptyClause())->toBeTrue();
    expect($structure->getLoopBody())->toHaveCount(3);
    expect($structure->getEmptyBody())->toHaveCount(1);

    expect($forElse)->toBe($structure->constructedFrom);

    $loopBody = $structure->getLoopBody();
    expect($loopBody[0])->toBeInstanceOf(LiteralNode::class);
    expect($loopBody[1])->toBeInstanceOf(EchoNode::class);
    expect($loopBody[2])->toBeInstanceOf(LiteralNode::class);

    $emptyBody = $structure->getEmptyBody();
    expect($emptyBody[0])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('No users2<', $emptyBody[0]->content);
});

test('for else without empty', function () {
    $template = <<<'EOT'
@forelse ($users as $user)
    <li>{{ $user->name }}</li>
@endforelse
EOT;
    $doc = $this->getDocument($template);
    $doc->resolveStructures();

    $forElse = $doc->findDirectiveByName('forelse');
    expect($forElse)->not->toBeNull();
    expect($forElse->isStructure)->toBeTrue();
    expect($forElse->structure)->not->toBeNull();

    /** @var ForElse $structure */
    $structure = $forElse->structure;
    expect($structure->hasEmptyClause())->toBeFalse();
    expect($structure->loopBody)->toHaveCount(3);
    expect($structure->emptyBody)->toHaveCount(0);
    expect($structure->isValid())->toBeFalse();

    expect($structure->loopBody[0])->toBeInstanceOf(LiteralNode::class);
    expect($structure->loopBody[1])->toBeInstanceOf(EchoNode::class);
    expect($structure->loopBody[2])->toBeInstanceOf(LiteralNode::class);
});

test('for else with nested for else without empty', function () {
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
    expect($forElseStatements)->toHaveCount(2);

    $forElse = $forElseStatements[0];

    /** @var ForElse $structure */
    $structure = $forElse->structure;
    expect($structure->hasEmptyClause())->toBeTrue();
    expect($structure->getLoopBody())->toHaveCount(5);
    expect($structure->getEmptyBody())->toHaveCount(1);

    expect($forElse)->toBe($structure->constructedFrom);

    $loopBody = $structure->getLoopBody();
    expect($loopBody[0])->toBeInstanceOf(LiteralNode::class);
    expect($loopBody[1])->toBeInstanceOf(EchoNode::class);
    expect($loopBody[2])->toBeInstanceOf(LiteralNode::class);
    expect($loopBody[3])->toBeInstanceOf(DirectiveNode::class);
    expect($loopBody[4])->toBeInstanceOf(LiteralNode::class);

    $emptyBody = $structure->getEmptyBody();
    expect($emptyBody[0])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('No users<', $emptyBody[0]->content);

    $forElse = $forElseStatements[1];

    expect($forElse)->not->toBeNull();
    expect($forElse->isStructure)->toBeTrue();
    expect($forElse->structure)->not->toBeNull();

    /** @var ForElse $structure */
    $structure = $forElse->structure;
    expect($structure->hasEmptyClause())->toBeFalse();
    expect($structure->loopBody)->toHaveCount(3);
    expect($structure->emptyBody)->toHaveCount(0);

    expect($structure->loopBody[0])->toBeInstanceOf(LiteralNode::class);
    expect($structure->loopBody[1])->toBeInstanceOf(EchoNode::class);
    expect($structure->loopBody[2])->toBeInstanceOf(LiteralNode::class);
});

test('for else with multiple empty directives', function () {
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
    expect($forElse->isValid())->toBeFalse();
    expect($forElse->emptyDirective)->not->toBeNull();
    expect($emptyBody)->toHaveCount(3);
    expect($emptyBody[0])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('Empty One', $emptyBody[0]->content);
    expect($emptyBody[1])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($emptyBody[1], 'empty');
    expect($emptyBody[2])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('Empty Two', $emptyBody[2]->content);
    $this->assertNotEquals($forElse->emptyDirective, $emptyBody[2]);
    expect($forElse->getEmptyDirectiveCount())->toBe(2);
});
