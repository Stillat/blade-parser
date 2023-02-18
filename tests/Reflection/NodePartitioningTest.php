<?php

namespace Stillat\BladeParser\Tests\Reflection;

use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\LiteralNode;
use Stillat\BladeParser\Tests\ParserTestCase;

class NodePartitioningTest extends ParserTestCase
{
    public function testNodePartitioning()
    {
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
        $this->assertNotNull($switch);
        $this->assertCount(11, $switch->getNodes());

        $partitions = $switch->getNodes()->partitionOnDirectives(['case', 'default']);
        $this->assertTrue($partitions->hasLeadingNodes());
        $this->assertTrue($partitions->hasPartitions());
        $this->assertCount(1, $partitions->leadingNodes);

        $this->assertInstanceOf(LiteralNode::class, $partitions->leadingNodes[0]);
        $this->assertStringContainsString('Leading Node Content', $partitions->leadingNodes[0]->content);
        $this->assertCount(3, $partitions->partitions);

        $firstPartition = $partitions->partitions[0];
        $this->assertCount(4, $firstPartition);
        $this->assertInstanceOf(DirectiveNode::class, $firstPartition[0]);
        $this->assertDirectiveContent($firstPartition[0], 'case', '(1)');
        $this->assertInstanceOf(LiteralNode::class, $firstPartition[1]);
        $this->assertStringContainsString('First case...', $firstPartition[1]->content);
        $this->assertInstanceOf(DirectiveNode::class, $firstPartition[2]);
        $this->assertDirectiveContent($firstPartition[2], 'break');
        $this->assertInstanceOf(LiteralNode::class, $firstPartition[3]);

        $secondPartition = $partitions->partitions[1];
        $this->assertCount(4, $secondPartition);
        $this->assertInstanceOf(DirectiveNode::class, $secondPartition[0]);
        $this->assertDirectiveContent($secondPartition[0], 'case', '(2)');
        $this->assertInstanceOf(LiteralNode::class, $secondPartition[1]);
        $this->assertStringContainsString('Second case...', $secondPartition[1]->content);
        $this->assertInstanceOf(DirectiveNode::class, $secondPartition[2]);
        $this->assertDirectiveContent($secondPartition[2], 'break');
        $this->assertInstanceOf(LiteralNode::class, $secondPartition[3]);

        $thirdPartition = $partitions->partitions[2];
        $this->assertCount(2, $thirdPartition);
        $this->assertInstanceOf(DirectiveNode::class, $thirdPartition[0]);
        $this->assertDirectiveContent($thirdPartition[0], 'default');
        $this->assertInstanceOf(LiteralNode::class, $thirdPartition[1]);
        $this->assertStringContainsString('Default case...', $thirdPartition[1]->content);
    }

    public function testNestedNodePartitioning()
    {
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
        $this->assertCount(2, $switchStatements);

        /** @var DirectiveNode $switch */
        $switch = $switchStatements[0];

        $this->assertNotNull($switch);
        $this->assertCount(13, $switch->getNodes());

        $partitions = $switch->getNodes()->partitionOnDirectives(['case', 'default']);
        $this->assertTrue($partitions->hasLeadingNodes());
        $this->assertTrue($partitions->hasPartitions());
        $this->assertCount(1, $partitions->leadingNodes);

        $this->assertInstanceOf(LiteralNode::class, $partitions->leadingNodes[0]);
        $this->assertStringContainsString('Leading Node Content', $partitions->leadingNodes[0]->content);
        $this->assertCount(3, $partitions->partitions);

        $firstPartition = $partitions->partitions[0];
        $this->assertCount(6, $firstPartition);
        $this->assertInstanceOf(DirectiveNode::class, $firstPartition[0]);
        $this->assertDirectiveContent($firstPartition[0], 'case', '(1)');
        $this->assertInstanceOf(LiteralNode::class, $firstPartition[1]);
        $this->assertStringContainsString('First case...', $firstPartition[1]->content);
        $this->assertInstanceOf(DirectiveNode::class, $firstPartition[2]);
        $this->assertDirectiveContent($firstPartition[2], 'switch', '($i2)');
        $this->assertInstanceOf(LiteralNode::class, $firstPartition[3]);
        $this->assertInstanceOf(DirectiveNode::class, $firstPartition[4]);
        $this->assertInstanceOf(LiteralNode::class, $firstPartition[5]);

        $secondPartition = $partitions->partitions[1];
        $this->assertCount(4, $secondPartition);
        $this->assertInstanceOf(DirectiveNode::class, $secondPartition[0]);
        $this->assertDirectiveContent($secondPartition[0], 'case', '(2)');
        $this->assertInstanceOf(LiteralNode::class, $secondPartition[1]);
        $this->assertStringContainsString('Second case...', $secondPartition[1]->content);
        $this->assertInstanceOf(DirectiveNode::class, $secondPartition[2]);
        $this->assertDirectiveContent($secondPartition[2], 'break');
        $this->assertInstanceOf(LiteralNode::class, $secondPartition[3]);

        $thirdPartition = $partitions->partitions[2];
        $this->assertCount(2, $thirdPartition);
        $this->assertInstanceOf(DirectiveNode::class, $thirdPartition[0]);
        $this->assertDirectiveContent($thirdPartition[0], 'default');
        $this->assertInstanceOf(LiteralNode::class, $thirdPartition[1]);
        $this->assertStringContainsString('Default case...', $thirdPartition[1]->content);

        /** @var DirectiveNode $switch */
        $switch = $switchStatements[1];

        $this->assertNotNull($switch);
        $this->assertCount(11, $switch->getNodes());

        $partitions = $switch->getNodes()->partitionOnDirectives(['case', 'default']);
        $this->assertTrue($partitions->hasLeadingNodes());
        $this->assertTrue($partitions->hasPartitions());
        $this->assertCount(1, $partitions->leadingNodes);

        $this->assertInstanceOf(LiteralNode::class, $partitions->leadingNodes[0]);
        $this->assertStringContainsString('Leading Node Content Two', $partitions->leadingNodes[0]->content);
        $this->assertCount(3, $partitions->partitions);

        $firstPartition = $partitions->partitions[0];
        $this->assertCount(4, $firstPartition);
        $this->assertInstanceOf(DirectiveNode::class, $firstPartition[0]);
        $this->assertDirectiveContent($firstPartition[0], 'case', '(2.1)');
        $this->assertInstanceOf(LiteralNode::class, $firstPartition[1]);
        $this->assertStringContainsString('First case two...', $firstPartition[1]->content);
        $this->assertInstanceOf(DirectiveNode::class, $firstPartition[2]);
        $this->assertDirectiveContent($firstPartition[2], 'break');
        $this->assertInstanceOf(LiteralNode::class, $firstPartition[3]);

        $secondPartition = $partitions->partitions[1];
        $this->assertCount(4, $secondPartition);
        $this->assertInstanceOf(DirectiveNode::class, $secondPartition[0]);
        $this->assertDirectiveContent($secondPartition[0], 'case', '(2.2)');
        $this->assertInstanceOf(LiteralNode::class, $secondPartition[1]);
        $this->assertStringContainsString('Second case two...', $secondPartition[1]->content);
        $this->assertInstanceOf(DirectiveNode::class, $secondPartition[2]);
        $this->assertDirectiveContent($secondPartition[2], 'break');
        $this->assertInstanceOf(LiteralNode::class, $secondPartition[3]);

        $thirdPartition = $partitions->partitions[2];
        $this->assertCount(2, $thirdPartition);
        $this->assertInstanceOf(DirectiveNode::class, $thirdPartition[0]);
        $this->assertDirectiveContent($thirdPartition[0], 'default');
        $this->assertInstanceOf(LiteralNode::class, $thirdPartition[1]);
        $this->assertStringContainsString('Default case two...', $thirdPartition[1]->content);
    }

    public function testTripleNestedNodePartitioning()
    {
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
        $this->assertCount(3, $switchStatements);

        /** @var DirectiveNode $switch */
        $switch = $switchStatements[0];

        $this->assertNotNull($switch);
        $this->assertCount(13, $switch->getNodes());

        $partitions = $switch->getNodes()->partitionOnDirectives(['case', 'default']);
        $this->assertTrue($partitions->hasLeadingNodes());
        $this->assertTrue($partitions->hasPartitions());
        $this->assertCount(1, $partitions->leadingNodes);

        $this->assertInstanceOf(LiteralNode::class, $partitions->leadingNodes[0]);
        $this->assertStringContainsString('Leading Node Content', $partitions->leadingNodes[0]->content);
        $this->assertCount(3, $partitions->partitions);

        $firstPartition = $partitions->partitions[0];
        $this->assertCount(6, $firstPartition);
        $this->assertInstanceOf(DirectiveNode::class, $firstPartition[0]);
        $this->assertDirectiveContent($firstPartition[0], 'case', '(1)');
        $this->assertInstanceOf(LiteralNode::class, $firstPartition[1]);
        $this->assertStringContainsString('First case...', $firstPartition[1]->content);
        $this->assertInstanceOf(DirectiveNode::class, $firstPartition[2]);
        $this->assertDirectiveContent($firstPartition[2], 'switch', '($i2)');
        $this->assertInstanceOf(LiteralNode::class, $firstPartition[3]);
        $this->assertInstanceOf(DirectiveNode::class, $firstPartition[4]);
        $this->assertInstanceOf(LiteralNode::class, $firstPartition[5]);

        $secondPartition = $partitions->partitions[1];
        $this->assertCount(4, $secondPartition);
        $this->assertInstanceOf(DirectiveNode::class, $secondPartition[0]);
        $this->assertDirectiveContent($secondPartition[0], 'case', '(2)');
        $this->assertInstanceOf(LiteralNode::class, $secondPartition[1]);
        $this->assertStringContainsString('Second case...', $secondPartition[1]->content);
        $this->assertInstanceOf(DirectiveNode::class, $secondPartition[2]);
        $this->assertDirectiveContent($secondPartition[2], 'break');
        $this->assertInstanceOf(LiteralNode::class, $secondPartition[3]);

        $thirdPartition = $partitions->partitions[2];
        $this->assertCount(2, $thirdPartition);
        $this->assertInstanceOf(DirectiveNode::class, $thirdPartition[0]);
        $this->assertDirectiveContent($thirdPartition[0], 'default');
        $this->assertInstanceOf(LiteralNode::class, $thirdPartition[1]);
        $this->assertStringContainsString('Default case...', $thirdPartition[1]->content);

        /** @var DirectiveNode $switch */
        $switch = $switchStatements[1];

        $this->assertNotNull($switch);
        $this->assertCount(13, $switch->getNodes());

        $partitions = $switch->getNodes()->partitionOnDirectives(['case', 'default']);
        $this->assertTrue($partitions->hasLeadingNodes());
        $this->assertTrue($partitions->hasPartitions());
        $this->assertCount(1, $partitions->leadingNodes);

        $this->assertInstanceOf(LiteralNode::class, $partitions->leadingNodes[0]);
        $this->assertStringContainsString('Leading Node Content Two', $partitions->leadingNodes[0]->content);
        $this->assertCount(3, $partitions->partitions);

        $firstPartition = $partitions->partitions[0];
        $this->assertCount(6, $firstPartition);
        $this->assertInstanceOf(DirectiveNode::class, $firstPartition[0]);
        $this->assertDirectiveContent($firstPartition[0], 'case', '(2.1)');
        $this->assertInstanceOf(LiteralNode::class, $firstPartition[1]);
        $this->assertStringContainsString('First case two...', $firstPartition[1]->content);
        $this->assertInstanceOf(DirectiveNode::class, $firstPartition[2]);
        $this->assertDirectiveContent($firstPartition[2], 'switch');
        $this->assertInstanceOf(LiteralNode::class, $firstPartition[3]);
        $this->assertDirectiveContent($firstPartition[4], 'break');

        $secondPartition = $partitions->partitions[1];
        $this->assertCount(4, $secondPartition);
        $this->assertInstanceOf(DirectiveNode::class, $secondPartition[0]);
        $this->assertDirectiveContent($secondPartition[0], 'case', '(2.2)');
        $this->assertInstanceOf(LiteralNode::class, $secondPartition[1]);
        $this->assertStringContainsString('Second case two...', $secondPartition[1]->content);
        $this->assertInstanceOf(DirectiveNode::class, $secondPartition[2]);
        $this->assertDirectiveContent($secondPartition[2], 'break');
        $this->assertInstanceOf(LiteralNode::class, $secondPartition[3]);

        $thirdPartition = $partitions->partitions[2];
        $this->assertCount(2, $thirdPartition);
        $this->assertInstanceOf(DirectiveNode::class, $thirdPartition[0]);
        $this->assertDirectiveContent($thirdPartition[0], 'default');
        $this->assertInstanceOf(LiteralNode::class, $thirdPartition[1]);
        $this->assertStringContainsString('Default case two...', $thirdPartition[1]->content);

        /** @var DirectiveNode $switch */
        $switch = $switchStatements[2];

        $this->assertNotNull($switch);
        $this->assertCount(11, $switch->getNodes());

        $partitions = $switch->getNodes()->partitionOnDirectives(['case', 'default']);
        $this->assertTrue($partitions->hasLeadingNodes());
        $this->assertTrue($partitions->hasPartitions());
        $this->assertCount(1, $partitions->leadingNodes);

        $this->assertInstanceOf(LiteralNode::class, $partitions->leadingNodes[0]);
        $this->assertStringContainsString('Leading Node Content Three', $partitions->leadingNodes[0]->content);
        $this->assertCount(3, $partitions->partitions);

        $firstPartition = $partitions->partitions[0];
        $this->assertCount(4, $firstPartition);
        $this->assertInstanceOf(DirectiveNode::class, $firstPartition[0]);
        $this->assertDirectiveContent($firstPartition[0], 'case', '(3.1)');
        $this->assertInstanceOf(LiteralNode::class, $firstPartition[1]);
        $this->assertStringContainsString('First case three...', $firstPartition[1]->content);
        $this->assertInstanceOf(DirectiveNode::class, $firstPartition[2]);
        $this->assertDirectiveContent($firstPartition[2], 'break');
        $this->assertInstanceOf(LiteralNode::class, $firstPartition[3]);

        $secondPartition = $partitions->partitions[1];
        $this->assertCount(4, $secondPartition);
        $this->assertInstanceOf(DirectiveNode::class, $secondPartition[0]);
        $this->assertDirectiveContent($secondPartition[0], 'case', '(3.2)');
        $this->assertInstanceOf(LiteralNode::class, $secondPartition[1]);
        $this->assertStringContainsString('Second case three...', $secondPartition[1]->content);
        $this->assertInstanceOf(DirectiveNode::class, $secondPartition[2]);
        $this->assertDirectiveContent($secondPartition[2], 'break');
        $this->assertInstanceOf(LiteralNode::class, $secondPartition[3]);

        $thirdPartition = $partitions->partitions[2];
        $this->assertCount(2, $thirdPartition);
        $this->assertInstanceOf(DirectiveNode::class, $thirdPartition[0]);
        $this->assertDirectiveContent($thirdPartition[0], 'default');
        $this->assertInstanceOf(LiteralNode::class, $thirdPartition[1]);
        $this->assertStringContainsString('Default case three...', $thirdPartition[1]->content);
    }
}
