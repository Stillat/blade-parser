<?php

namespace Stillat\BladeParser\Tests\Structures;

use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Tests\ParserTestCase;

class DirectivePairTest extends ParserTestCase
{
    public function testBasicDirectivePairing()
    {
        $template = <<<'EOT'
@section ('section_name')

@endsection
EOT;
        $document = $this->getDocument($template);
        $document->resolveStructures();

        $section = $document->findDirectiveByName('section');
        $endSection = $document->findDirectiveByName('endsection');

        $this->assertDirectivesArePaired($section, $endSection);
    }

    public function testNestedDirectivePairing()
    {
        $template = <<<'EOT'
@section ('section_name')
    @section ('section_name')
        @section ('section_name')
            @section ('section_name')
                @section ('section_name')
                    @section ('section_name')
                        @section ('section_name')
                            @section ('section_name')
                                @section ('section_name')
                                    @section ('section_name')
                                    
                                    @endsection
                                @endsection
                            @endsection
                        @endsection
                    @endsection
                @endsection
            @endsection
        @endsection
    @endsection
@endsection
EOT;
        $document = $this->getDocument($template);
        $document->resolveStructures();

        $this->assertManyDirectivesArePaired($document->getDirectives()->all());
    }

    public function testConditionStressTest()
    {
        $template = str_repeat(" @if ('directive_params')\n", 25);
        $template .= str_repeat(" @endIf\n", 25);

        $doc = $this->getDocument($template);
        $doc->resolveStructures();

        $this->assertManyDirectivesArePaired($doc->getDirectives()->values()->all());
    }

    /**
     * @dataProvider coreDirectivePairings
     *
     * @return void
     */
    public function testCoreDirectivePairing(string $open, string $close)
    {
        // Build up a dynamic nested template.
        $template = str_repeat(" @{$open} ('directive_params')\n", 50);
        $template .= str_repeat(" @{$close} \n", 50);

        $document = $this->getDocument($template);
        $document->resolveStructures();

        $this->assertManyDirectivesArePaired($document->getDirectives()->values()->all());
    }

    public function testUltimateClosingDirective()
    {
        $template = <<<'EOT'
D1 @if ($something)

D2 @elseif ($somethingElse)

D3 @elseif ($anotherThing)

D4 @endif
EOT;
        $doc = $this->getDocument($template);
        $doc->resolveStructures();
        $directives = $doc->getDirectives();

        /** @var DirectiveNode $d1 */
        $d1 = $directives[0];

        /** @var DirectiveNode $d2 */
        $d2 = $directives[1];

        /** @var DirectiveNode $d3 */
        $d3 = $directives[2];

        /** @var DirectiveNode $d4 */
        $d4 = $directives[3];

        $this->assertDirectivesArePaired($d1, $d2);
        $this->assertEquals($d4, $d1->getFinalClosingDirective());

        $this->assertDirectivesArePaired($d2, $d3);
        $this->assertEquals($d4, $d2->getFinalClosingDirective());

        $this->assertDirectivesArePaired($d3, $d4);
        $this->assertEquals($d4, $d3->getFinalClosingDirective());

        $this->assertNull($d4->getFinalClosingDirective());
    }

    public function testRootOpeningDirective()
    {
        $template = <<<'EOT'
D1 @if ($something)

D2 @elseif ($somethingElse)

D3 @elseif ($anotherThing)

D4 @endif
EOT;
        $doc = $this->getDocument($template);
        $doc->resolveStructures();
        $directives = $doc->getDirectives();

        /** @var DirectiveNode $d1 */
        $d1 = $directives[0];

        /** @var DirectiveNode $d2 */
        $d2 = $directives[1];

        /** @var DirectiveNode $d3 */
        $d3 = $directives[2];

        /** @var DirectiveNode $d4 */
        $d4 = $directives[3];

        $this->assertDirectivesArePaired($d1, $d2);
        $this->assertSame($d1, $d2->getRootOpeningDirective());

        $this->assertDirectivesArePaired($d2, $d3);
        $this->assertSame($d1, $d3->getRootOpeningDirective());

        $this->assertDirectivesArePaired($d3, $d4);
        $this->assertSame($d1, $d4->getRootOpeningDirective());

        $this->assertNull($d1->getRootOpeningDirective());
    }

    public static function coreDirectivePairings()
    {
        return [
            ['once', 'endOnce'],
            ['slot', 'endSlot'],
            ['push', 'endPush'],
            ['pushOnce', 'endpushonce'],
            ['componentFirst', 'endComponentFirst'],
            ['error', 'enderror'],
            ['prependOnce', 'endprependOnce'],
            ['prepend', 'endprepend'],
            ['while', 'endwhile'],
        ];
    }

    /**
     * @param  DirectiveNode[]  $directives
     */
    private function assertManyDirectivesArePaired(array $directives): void
    {
        $directiveCount = count($directives);
        $limit = $directiveCount / 2;

        for ($i = 0; $i < $limit; $i++) {
            $closeIndex = $directiveCount - ($i + 1);
            $openDirective = $directives[$i];
            $closeDirective = $directives[$closeIndex];

            $this->assertDirectivesArePaired($openDirective, $closeDirective, $openDirective->content.' <> ', $closeDirective->content);
        }
    }
}
