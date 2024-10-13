<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Nodes\DirectiveNode;

test('basic directive pairing', function () {
    $template = <<<'EOT'
@section ('section_name')

@endsection
EOT;
    $document = $this->getDocument($template);
    $document->resolveStructures();

    $section = $document->findDirectiveByName('section');
    $endSection = $document->findDirectiveByName('endsection');

    $this->assertDirectivesArePaired($section, $endSection);
});

test('nested directive pairing', function () {
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

    assertManyDirectivesArePaired($document->getDirectives()->all());
});

test('condition stress test', function () {
    $template = str_repeat(" @if ('directive_params')\n", 25);
    $template .= str_repeat(" @endIf\n", 25);

    $doc = $this->getDocument($template);
    $doc->resolveStructures();

    assertManyDirectivesArePaired($doc->getDirectives()->values()->all());
});

test('core directive pairing', function (string $open, string $close) {
    // Build up a dynamic nested template.
    $template = str_repeat(" @{$open} ('directive_params')\n", 50);
    $template .= str_repeat(" @{$close} \n", 50);

    $document = $this->getDocument($template);
    $document->resolveStructures();

    assertManyDirectivesArePaired($document->getDirectives()->values()->all());
})->with('coreDirectivePairings');

test('ultimate closing directive', function () {
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
    expect($d1->getFinalClosingDirective())->toEqual($d4);

    $this->assertDirectivesArePaired($d2, $d3);
    expect($d2->getFinalClosingDirective())->toEqual($d4);

    $this->assertDirectivesArePaired($d3, $d4);
    expect($d3->getFinalClosingDirective())->toEqual($d4);

    expect($d4->getFinalClosingDirective())->toBeNull();
});

test('root opening directive', function () {
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
    expect($d2->getRootOpeningDirective())->toBe($d1);

    $this->assertDirectivesArePaired($d2, $d3);
    expect($d3->getRootOpeningDirective())->toBe($d1);

    $this->assertDirectivesArePaired($d3, $d4);
    expect($d4->getRootOpeningDirective())->toBe($d1);

    expect($d1->getRootOpeningDirective())->toBeNull();
});

dataset('coreDirectivePairings', function () {
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
});
