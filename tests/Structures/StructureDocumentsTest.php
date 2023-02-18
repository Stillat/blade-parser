<?php

namespace Stillat\BladeParser\Tests\Structures;

use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Tests\ParserTestCase;

class StructureDocumentsTest extends ParserTestCase
{
    public function testDirectivePairStructureDocuments()
    {
        $template = <<<'EOT'
One
@section ('section_name')
    Two
@endsection Three
EOT;
        $doc = $this->getDocument($template);
        $doc->resolveStructures();

        $expected = <<<'EXPECTED'

    Two

EXPECTED;

        $outerText = <<<'OUTER'
@section ('section_name')
    Two
@endsection
OUTER;

        $this->assertSame($expected, $doc->findDirectiveByName('section')->getInnerDocumentContent());
        $this->assertSame($outerText, $doc->findDirectiveByName('section')->getOuterDocumentContent());
    }

    public function testSwitchStatementStructureDocuments()
    {
        $template = <<<'EOT'
@switch($i)
    @case(1)
        First case...
        
        @switch($i)
            @case(1)
                First case...
                @break
         
            @case(2)
                Second case...
                @break
         
            @default
                Default case...
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
        /** @var DirectiveNode[] $switchStatements */
        $switchStatements = $doc->findDirectivesByName('switch');
        $this->assertCount(2, $switchStatements);

        $doc1 = <<<'EXPECTED'

    @case(1)
        First case...
        
        @switch($i)
            @case(1)
                First case...
                @break
         
            @case(2)
                Second case...
                @break
         
            @default
                Default case...
        @endswitch
        
        @break
 
    @case(2)
        Second case...
        @break
 
    @default
        Default case...

EXPECTED;

        $doc2 = <<<'EXPECTED'

            @case(1)
                First case...
                @break
         
            @case(2)
                Second case...
                @break
         
            @default
                Default case...
        
EXPECTED;

        $nestedOuterText = <<<'EXPECTED'
@switch($i)
            @case(1)
                First case...
                @break
         
            @case(2)
                Second case...
                @break
         
            @default
                Default case...
        @endswitch
EXPECTED;

        $this->assertSame($doc1, $switchStatements[0]->getInnerDocumentContent());
        $this->assertSame($doc2, $switchStatements[1]->getInnerDocumentContent());
        $this->assertSame($nestedOuterText, $switchStatements[1]->getOuterDocumentContent());
    }

    public function testComponentTagDocumentText()
    {
        $template = <<<'EOT'
One
<x-alert>
    The inner text
    <x-alert message="a message">Nested inner text</x-alert>
</x-alert>
Two
EOT;
        $doc = $this->getDocument($template);
        $doc->resolveStructures();
        $components = $doc->getOpeningComponentTags();
        $this->assertCount(2, $components);
        /** @var ComponentNode $c1 */
        $c1 = $components[0];
        /** @var ComponentNode $c2 */
        $c2 = $components[1];

        $c1InnerText = <<<'EXPECTED'

    The inner text
    <x-alert message="a message">Nested inner text</x-alert>

EXPECTED;
        $this->assertSame($c1InnerText, $c1->getInnerDocumentContent());

        $c1OuterText = <<<'EXPECTED'
<x-alert>
    The inner text
    <x-alert message="a message">Nested inner text</x-alert>
</x-alert>
EXPECTED;
        $this->assertSame($c1OuterText, $c1->getOuterDocumentContent());

        $this->assertSame('Nested inner text', $c2->getInnerDocumentContent());
        $this->assertSame('<x-alert message="a message">Nested inner text</x-alert>', $c2->getOuterDocumentContent());
    }
}
