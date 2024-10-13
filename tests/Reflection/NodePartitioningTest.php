<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\LiteralNode;

test('node partitioning', function () {
    $template = <<<'EOT'
@switch($i)
    Leading Node Content
    @case(1)
        First case...
        @break
 
    @case(2)
        Second case...
        @break
 
    @default
        Default case...
@endswitch
EOT;
    $doc = $this->getDocument($template);
    $doc->resolveStructures();
    $switch = $doc->findDirectiveByName('switch');
    expect($switch)->not->toBeNull();
    expect($switch->getNodes())->toHaveCount(11);

    $partitions = $switch->getNodes()->partitionOnDirectives(['case', 'default']);
    expect($partitions->hasLeadingNodes())->toBeTrue();
    expect($partitions->hasPartitions())->toBeTrue();
    expect($partitions->leadingNodes)->toHaveCount(1);

    expect($partitions->leadingNodes[0])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('Leading Node Content', $partitions->leadingNodes[0]->content);
    expect($partitions->partitions)->toHaveCount(3);

    $firstPartition = $partitions->partitions[0];
    expect($firstPartition)->toHaveCount(4);
    expect($firstPartition[0])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($firstPartition[0], 'case', '(1)');
    expect($firstPartition[1])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('First case...', $firstPartition[1]->content);
    expect($firstPartition[2])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($firstPartition[2], 'break');
    expect($firstPartition[3])->toBeInstanceOf(LiteralNode::class);

    $secondPartition = $partitions->partitions[1];
    expect($secondPartition)->toHaveCount(4);
    expect($secondPartition[0])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($secondPartition[0], 'case', '(2)');
    expect($secondPartition[1])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('Second case...', $secondPartition[1]->content);
    expect($secondPartition[2])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($secondPartition[2], 'break');
    expect($secondPartition[3])->toBeInstanceOf(LiteralNode::class);

    $thirdPartition = $partitions->partitions[2];
    expect($thirdPartition)->toHaveCount(2);
    expect($thirdPartition[0])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($thirdPartition[0], 'default');
    expect($thirdPartition[1])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('Default case...', $thirdPartition[1]->content);
});

test('nested node partitioning', function () {
    $template = <<<'EOT'
@switch($i)
    Leading Node Content
    @case(1)
        First case...
        
        @switch($i2)
            Leading Node Content Two
            @case(2.1)
                First case two...
                @break
         
            @case(2.2)
                Second case two...
                @break
         
            @default
                Default case two...
        @endswitch
        
        @break
 
    @case(2)
        Second case...
        @break
 
    @default
        Default case...
@endswitch
EOT;
    $doc = $this->getDocument($template);
    $doc->resolveStructures();

    $switchStatements = $doc->findDirectivesByName('switch');
    expect($switchStatements)->toHaveCount(2);

    /** @var DirectiveNode $switch */
    $switch = $switchStatements[0];

    expect($switch)->not->toBeNull();
    expect($switch->getNodes())->toHaveCount(13);

    $partitions = $switch->getNodes()->partitionOnDirectives(['case', 'default']);
    expect($partitions->hasLeadingNodes())->toBeTrue();
    expect($partitions->hasPartitions())->toBeTrue();
    expect($partitions->leadingNodes)->toHaveCount(1);

    expect($partitions->leadingNodes[0])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('Leading Node Content', $partitions->leadingNodes[0]->content);
    expect($partitions->partitions)->toHaveCount(3);

    $firstPartition = $partitions->partitions[0];
    expect($firstPartition)->toHaveCount(6);
    expect($firstPartition[0])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($firstPartition[0], 'case', '(1)');
    expect($firstPartition[1])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('First case...', $firstPartition[1]->content);
    expect($firstPartition[2])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($firstPartition[2], 'switch', '($i2)');
    expect($firstPartition[3])->toBeInstanceOf(LiteralNode::class);
    expect($firstPartition[4])->toBeInstanceOf(DirectiveNode::class);
    expect($firstPartition[5])->toBeInstanceOf(LiteralNode::class);

    $secondPartition = $partitions->partitions[1];
    expect($secondPartition)->toHaveCount(4);
    expect($secondPartition[0])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($secondPartition[0], 'case', '(2)');
    expect($secondPartition[1])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('Second case...', $secondPartition[1]->content);
    expect($secondPartition[2])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($secondPartition[2], 'break');
    expect($secondPartition[3])->toBeInstanceOf(LiteralNode::class);

    $thirdPartition = $partitions->partitions[2];
    expect($thirdPartition)->toHaveCount(2);
    expect($thirdPartition[0])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($thirdPartition[0], 'default');
    expect($thirdPartition[1])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('Default case...', $thirdPartition[1]->content);

    /** @var DirectiveNode $switch */
    $switch = $switchStatements[1];

    expect($switch)->not->toBeNull();
    expect($switch->getNodes())->toHaveCount(11);

    $partitions = $switch->getNodes()->partitionOnDirectives(['case', 'default']);
    expect($partitions->hasLeadingNodes())->toBeTrue();
    expect($partitions->hasPartitions())->toBeTrue();
    expect($partitions->leadingNodes)->toHaveCount(1);

    expect($partitions->leadingNodes[0])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('Leading Node Content Two', $partitions->leadingNodes[0]->content);
    expect($partitions->partitions)->toHaveCount(3);

    $firstPartition = $partitions->partitions[0];
    expect($firstPartition)->toHaveCount(4);
    expect($firstPartition[0])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($firstPartition[0], 'case', '(2.1)');
    expect($firstPartition[1])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('First case two...', $firstPartition[1]->content);
    expect($firstPartition[2])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($firstPartition[2], 'break');
    expect($firstPartition[3])->toBeInstanceOf(LiteralNode::class);

    $secondPartition = $partitions->partitions[1];
    expect($secondPartition)->toHaveCount(4);
    expect($secondPartition[0])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($secondPartition[0], 'case', '(2.2)');
    expect($secondPartition[1])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('Second case two...', $secondPartition[1]->content);
    expect($secondPartition[2])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($secondPartition[2], 'break');
    expect($secondPartition[3])->toBeInstanceOf(LiteralNode::class);

    $thirdPartition = $partitions->partitions[2];
    expect($thirdPartition)->toHaveCount(2);
    expect($thirdPartition[0])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($thirdPartition[0], 'default');
    expect($thirdPartition[1])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('Default case two...', $thirdPartition[1]->content);
});

test('triple nested node partitioning', function () {
    $template = <<<'EOT'
@switch($i)
    Leading Node Content
    @case(1)
        First case...
        
        @switch($i2)
            Leading Node Content Two
            @case(2.1)
                First case two...
                
                @switch($i3)
                    Leading Node Content Three
                    @case(3.1)
                        First case three...
                        @break
                 
                    @case(3.2)
                        Second case three...
                        @break
                 
                    @default
                        Default case three...
                @endswitch
                
                @break
         
            @case(2.2)
                Second case two...
                @break
         
            @default
                Default case two...
        @endswitch
        
        @break
 
    @case(2)
        Second case...
        @break
 
    @default
        Default case...
@endswitch
EOT;
    $doc = $this->getDocument($template);
    $doc->resolveStructures();

    $switchStatements = $doc->findDirectivesByName('switch');
    expect($switchStatements)->toHaveCount(3);

    /** @var DirectiveNode $switch */
    $switch = $switchStatements[0];

    expect($switch)->not->toBeNull();
    expect($switch->getNodes())->toHaveCount(13);

    $partitions = $switch->getNodes()->partitionOnDirectives(['case', 'default']);
    expect($partitions->hasLeadingNodes())->toBeTrue();
    expect($partitions->hasPartitions())->toBeTrue();
    expect($partitions->leadingNodes)->toHaveCount(1);

    expect($partitions->leadingNodes[0])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('Leading Node Content', $partitions->leadingNodes[0]->content);
    expect($partitions->partitions)->toHaveCount(3);

    $firstPartition = $partitions->partitions[0];
    expect($firstPartition)->toHaveCount(6);
    expect($firstPartition[0])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($firstPartition[0], 'case', '(1)');
    expect($firstPartition[1])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('First case...', $firstPartition[1]->content);
    expect($firstPartition[2])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($firstPartition[2], 'switch', '($i2)');
    expect($firstPartition[3])->toBeInstanceOf(LiteralNode::class);
    expect($firstPartition[4])->toBeInstanceOf(DirectiveNode::class);
    expect($firstPartition[5])->toBeInstanceOf(LiteralNode::class);

    $secondPartition = $partitions->partitions[1];
    expect($secondPartition)->toHaveCount(4);
    expect($secondPartition[0])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($secondPartition[0], 'case', '(2)');
    expect($secondPartition[1])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('Second case...', $secondPartition[1]->content);
    expect($secondPartition[2])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($secondPartition[2], 'break');
    expect($secondPartition[3])->toBeInstanceOf(LiteralNode::class);

    $thirdPartition = $partitions->partitions[2];
    expect($thirdPartition)->toHaveCount(2);
    expect($thirdPartition[0])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($thirdPartition[0], 'default');
    expect($thirdPartition[1])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('Default case...', $thirdPartition[1]->content);

    /** @var DirectiveNode $switch */
    $switch = $switchStatements[1];

    expect($switch)->not->toBeNull();
    expect($switch->getNodes())->toHaveCount(13);

    $partitions = $switch->getNodes()->partitionOnDirectives(['case', 'default']);
    expect($partitions->hasLeadingNodes())->toBeTrue();
    expect($partitions->hasPartitions())->toBeTrue();
    expect($partitions->leadingNodes)->toHaveCount(1);

    expect($partitions->leadingNodes[0])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('Leading Node Content Two', $partitions->leadingNodes[0]->content);
    expect($partitions->partitions)->toHaveCount(3);

    $firstPartition = $partitions->partitions[0];
    expect($firstPartition)->toHaveCount(6);
    expect($firstPartition[0])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($firstPartition[0], 'case', '(2.1)');
    expect($firstPartition[1])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('First case two...', $firstPartition[1]->content);
    expect($firstPartition[2])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($firstPartition[2], 'switch');
    expect($firstPartition[3])->toBeInstanceOf(LiteralNode::class);
    $this->assertDirectiveContent($firstPartition[4], 'break');

    $secondPartition = $partitions->partitions[1];
    expect($secondPartition)->toHaveCount(4);
    expect($secondPartition[0])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($secondPartition[0], 'case', '(2.2)');
    expect($secondPartition[1])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('Second case two...', $secondPartition[1]->content);
    expect($secondPartition[2])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($secondPartition[2], 'break');
    expect($secondPartition[3])->toBeInstanceOf(LiteralNode::class);

    $thirdPartition = $partitions->partitions[2];
    expect($thirdPartition)->toHaveCount(2);
    expect($thirdPartition[0])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($thirdPartition[0], 'default');
    expect($thirdPartition[1])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('Default case two...', $thirdPartition[1]->content);

    /** @var DirectiveNode $switch */
    $switch = $switchStatements[2];

    expect($switch)->not->toBeNull();
    expect($switch->getNodes())->toHaveCount(11);

    $partitions = $switch->getNodes()->partitionOnDirectives(['case', 'default']);
    expect($partitions->hasLeadingNodes())->toBeTrue();
    expect($partitions->hasPartitions())->toBeTrue();
    expect($partitions->leadingNodes)->toHaveCount(1);

    expect($partitions->leadingNodes[0])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('Leading Node Content Three', $partitions->leadingNodes[0]->content);
    expect($partitions->partitions)->toHaveCount(3);

    $firstPartition = $partitions->partitions[0];
    expect($firstPartition)->toHaveCount(4);
    expect($firstPartition[0])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($firstPartition[0], 'case', '(3.1)');
    expect($firstPartition[1])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('First case three...', $firstPartition[1]->content);
    expect($firstPartition[2])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($firstPartition[2], 'break');
    expect($firstPartition[3])->toBeInstanceOf(LiteralNode::class);

    $secondPartition = $partitions->partitions[1];
    expect($secondPartition)->toHaveCount(4);
    expect($secondPartition[0])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($secondPartition[0], 'case', '(3.2)');
    expect($secondPartition[1])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('Second case three...', $secondPartition[1]->content);
    expect($secondPartition[2])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($secondPartition[2], 'break');
    expect($secondPartition[3])->toBeInstanceOf(LiteralNode::class);

    $thirdPartition = $partitions->partitions[2];
    expect($thirdPartition)->toHaveCount(2);
    expect($thirdPartition[0])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($thirdPartition[0], 'default');
    expect($thirdPartition[1])->toBeInstanceOf(LiteralNode::class);
    $this->assertStringContainsString('Default case three...', $thirdPartition[1]->content);
});
