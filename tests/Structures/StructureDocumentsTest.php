<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Nodes\DirectiveNode;

test('directive pair structure documents', function () {
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

    expect($doc->findDirectiveByName('section')->getInnerDocumentContent())->toBe($expected);
    expect($doc->findDirectiveByName('section')->getOuterDocumentContent())->toBe($outerText);
});

test('switch statement structure documents', function () {
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
    expect($switchStatements)->toHaveCount(2);

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

    expect($switchStatements[0]->getInnerDocumentContent())->toBe($doc1);
    expect($switchStatements[1]->getInnerDocumentContent())->toBe($doc2);
    expect($switchStatements[1]->getOuterDocumentContent())->toBe($nestedOuterText);
});

test('component tag document text', function () {
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
    expect($components)->toHaveCount(2);

    /** @var ComponentNode $c1 */
    $c1 = $components[0];

    /** @var ComponentNode $c2 */
    $c2 = $components[1];

    $c1InnerText = <<<'EXPECTED'

    The inner text
    <x-alert message="a message">Nested inner text</x-alert>

EXPECTED;
    expect($c1->getInnerDocumentContent())->toBe($c1InnerText);

    $c1OuterText = <<<'EXPECTED'
<x-alert>
    The inner text
    <x-alert message="a message">Nested inner text</x-alert>
</x-alert>
EXPECTED;
    expect($c1->getOuterDocumentContent())->toBe($c1OuterText);

    expect($c2->getInnerDocumentContent())->toBe('Nested inner text');
    expect($c2->getOuterDocumentContent())->toBe('<x-alert message="a message">Nested inner text</x-alert>');
});
