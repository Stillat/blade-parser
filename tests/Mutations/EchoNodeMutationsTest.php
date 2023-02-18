<?php

namespace Stillat\BladeParser\Tests\Mutations;

use Stillat\BladeParser\Nodes\EchoNode;
use Stillat\BladeParser\Nodes\EchoType;
use Stillat\BladeParser\Tests\ParserTestCase;

class EchoNodeMutationsTest extends ParserTestCase
{
    public function testBasicEchoNodeMutations()
    {
        $doc = $this->getDocument('A{{ $echo }}B{{ $echoTwo }}C');
        /** @var EchoNode $echo */
        $echo = $doc->firstOfType(EchoNode::class);

        $echo->setInnerContent('$newVariable');
        $this->assertTrue($echo->isDirty());
        $this->assertSame('{{ $newVariable }}', (string) $echo);

        $expected = <<<'EOT'
A{{ $newVariable }}B{{ $echoTwo }}C
EOT;

        $this->assertSame($expected, (string) $doc);
    }

    public function testChangingEchoTypeMutations()
    {
        $doc = $this->getDocument('A{{ $echo }}B{{ $echoTwo }}C');
        /** @var EchoNode $echo */
        $echo = $doc->firstOfType(EchoNode::class);

        $echo->setInnerContent('$newVariable');
        $echo->setType(EchoType::RawEcho);
        $this->assertTrue($echo->isDirty());
        $this->assertSame(EchoType::RawEcho, $echo->type);
        $this->assertSame('{!! $newVariable !!}', (string) $echo);

        $expected = <<<'EOT'
A{!! $newVariable !!}B{{ $echoTwo }}C
EOT;

        $this->assertSame($expected, (string) $doc);
    }

    public function testBasicTripleEchoNodeMutations()
    {
        $doc = $this->getDocument('A{{{ $echo }}}B{{{ $echoTwo }}}C');
        /** @var EchoNode $echo */
        $echo = $doc->firstOfType(EchoNode::class);

        $echo->setInnerContent('$newVariable');
        $this->assertTrue($echo->isDirty());
        $this->assertSame('{{{ $newVariable }}}', (string) $echo);

        $expected = <<<'EOT'
A{{{ $newVariable }}}B{{{ $echoTwo }}}C
EOT;

        $this->assertSame($expected, (string) $doc);
    }

    public function testBasicRawEchoNodeMutations()
    {
        $doc = $this->getDocument('A{!! $echo !!}B{!! $echoTwo !!}C');
        /** @var EchoNode $echo */
        $echo = $doc->firstOfType(EchoNode::class);

        $echo->setInnerContent('$newVariable');
        $this->assertTrue($echo->isDirty());
        $this->assertSame('{!! $newVariable !!}', (string) $echo);

        $expected = <<<'EOT'
A{!! $newVariable !!}B{!! $echoTwo !!}C
EOT;

        $this->assertSame($expected, (string) $doc);
    }
}
