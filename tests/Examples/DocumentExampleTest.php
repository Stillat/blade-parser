<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Document\Document;
use \Stillat\BladeParser\Validation\AbstractNodeValidator;
use Stillat\BladeParser\Errors\ConstructContext;
use Stillat\BladeParser\Errors\ErrorType;
use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\EchoNode;
use Stillat\BladeParser\Nodes\LiteralNode;
use Stillat\BladeParser\Validation\ValidationResult;
use Stillat\BladeParser\Validation\Validators\InconsistentIndentationLevelValidator;


test('remove node', function () {
    $template = <<<'BLADE'
<?php $count = 1; ?>

The count is {{ $count }}.
BLADE;
    $doc = Document::fromText($template);

    // Remove the first <?php tag from the document.
    $doc->removeNode($doc->getPhpTags()->first());

    // Convert the document to Blade without the first <?php tag.
    $result = (string) $doc;

    // End
    $expected = <<<'EXPECTED'


The count is {{ $count }}.
EXPECTED;

    expect($result)->toBe($expected);
});

test('get echoes', function () {
    $template = <<<'BLADE'
    {{ $variableOne }}
    {{{ $variableTwo }}}
    {!! $variableThree !!}
BLADE;
    $doc = Document::fromText($template);

    // Returns 3
    $echoCount = $doc->getEchoes()->count();

    // end
    expect($echoCount)->toBe(3);
});

test('get php tags', function () {
    $template = <<<'BLADE'
    <?php $count = 1; ?>
    
        The count is: <?= $count ?>
BLADE;
    $doc = Document::fromText($template);

    // Returns 2
    $phpTagCount = $doc->getPhpTags()->count();

    // end
    expect($phpTagCount)->toBe(2);
});

test('get php blocks', function () {
    $template = <<<'BLADE'
    @php
        $count = 1;
    @endphp
    
        @php ($count++)
        
    @php
        echo "The count is: {$count}";
    @endphp
BLADE;
    $doc = Document::fromText($template);

    // Returns 2
    $phpBlockCount = $doc->getPhpBlocks()->count();

    // end
    expect($phpBlockCount)->toBe(2);
});

test('get verbatim blocks', function () {
    $template = <<<'BLADE'
    @verbatim
        {{ $hello }}
    @endverbatim
    
    Content
    
    @verbatim
        {{ $world }}
    @endverbatim
BLADE;
    $doc = Document::fromText($template);

    // Returns 2
    $verbatimCount = $doc->getVerbatimBlocks()->count();

    // end
    expect($verbatimCount)->toBe(2);
});

test('get literals', function () {
    $template = <<<'BLADE'
    One {{ $variable }} Two {{ $variable }}

BLADE;
    $doc = Document::fromText($template);

    // Returns 3
    $literalCount = $doc->getLiterals()->count();

    // end
    expect($literalCount)->toBe(3);
});

test('get directives', function () {
    $template = <<<'BLADE'
    @if ($value)
    
    @elseif ($anotherValue)
    
    @else
    
    @endif
BLADE;
    $doc = Document::fromText($template);

    // Returns 4
    $directiveCount = $doc->getDirectives()->count();

    // end
    expect($directiveCount)->toBe(4);
});

test('find directive by name', function () {
    $template = <<<'BLADE'
    @if ($something)
        @include ('another/file')
    @endif
BLADE;
    $doc = Document::fromText($template);

    // Returns 'another/file'
    $include = $doc->findDirectiveByName('include')
        ->arguments->getStringValue();

    // end
    expect($include)->toBe('another/file');
});

test('find directives by name', function () {
    $template = <<<'BLADE'
    @include ('file/one') @if ($something) @endif
        @include ('file/two')
            @include
BLADE;
    $doc = Document::fromText($template);

    // Returns 3
    $includeCount = $doc->findDirectivesByName('include')->count();

    // end
    expect($includeCount)->toBe(3);
});

test('has directive', function () {
    $template = <<<'BLADE'
    @extends ('layout')
    
    @section ('content')
    
    @endSection
BLADE;
    $doc = Document::fromText($template);

    // Returns true
    $hasExtends = $doc->hasDirective('extends');

    // Returns false
    $hasCan = $doc->hasDirective('can');

    // end
    expect($hasExtends)->toBeTrue();
    expect($hasCan)->toBeFalse();
});

test('get comments', function () {
    $template = <<<'BLADE'
    {{-- One --}}
        {{-- Two --}}
BLADE;
    $doc = Document::fromText($template);

    // Returns 2
    $commentCount = $doc->getComments()->count();

    // end
    expect($commentCount)->toBe(2);
});

test('get components', function () {
    $template = <<<'BLADE'
    <x-profile :$user />

    <x-alert></x-alert>
BLADE;
    $doc = Document::fromText($template);

    // Returns 3
    $componentCount = $doc->getComponents()->count();

    // end
    expect($componentCount)->toBe(3);
});

test('get opening component tags', function () {
    $template = <<<'BLADE'
    <x-profile :$user />

    <x-alert></x-alert>
BLADE;
    $doc = Document::fromText($template);

    // Returns 2
    $componentCount = $doc->getOpeningComponentTags()->count();

    // end
    expect($componentCount)->toBe(2);
});

test('find components by tag name', function () {
    $template = <<<'BLADE'
    <x-slot:name>
        <x:slot name="another-name">
            <x-profile />
        </x:slot>
    </x-slot>
BLADE;
    $doc = Document::fromText($template);

    // Returns 4
    $componentCount = $doc->findComponentsByTagName('slot')->count();

    // end
    expect($componentCount)->toBe(4);
});

test('find component by tag name', function () {
    $template = <<<'BLADE'
    <x-message content="message" />
    <x-profile />
BLADE;
    $doc = Document::fromText($template);

    $component = $doc->findComponentByTagName('message');

    // Returns 'message content="message" '
    $innerContent = $component->innerContent;

    // end
    expect($innerContent)->toBe('message content="message" ');
});

test('all of type', function () {
    $template = <<<'BLADE'

    {{ $hello }}

BLADE;
    $doc = Document::fromText($template);

    // Returns 2
    $count = $doc->allOfType(LiteralNode::class)->count();

    // end
    expect($count)->toBe(2);
});

test('all not of type', function () {
    $template = <<<'BLADE'

    {{ $hello }}

BLADE;
    $doc = Document::fromText($template);

    // Returns 2
    $count = $doc->allOfType(EchoNode::class)->count();

    // end
    expect($count)->toBe(1);
});

test('first of type', function () {
    $template = <<<'BLADE'
    @if
    @can
    @include
BLADE;
    $doc = Document::fromText($template);

    // Returns 'if'
    $content = $doc->firstOfType(DirectiveNode::class)->content;

    // end
    expect($content)->toBe('if');
});

test('last of type', function () {
    $template = <<<'BLADE'
    @if
    @can
    @include
BLADE;
    $doc = Document::fromText($template);

    // Returns 'include'
    $content = $doc->lastOfType(DirectiveNode::class)->content;

    // end
    expect($content)->toBe('include');
});

test('find node pattern', function () {
    $template = <<<'BLADE'
    {{ $one }} {{ $two }}
    {{ $three }} @if {{ $four }}
BLADE;
    $doc = Document::fromText($template);

    $pattern = [
        EchoNode::class,
        EchoNode::class,
    ];

    // Find all node sequences that contain
    // two echo nodes in a row.
    $results = $doc->findNodePattern($pattern);

    // Returns 2
    $resultCount = count($results);

    // Once complete, $text will contain two elements:
    //   0: {{ $one }} {{ $two }}
    //   1: {{ $two }} {{ $three }}
    $text = [];

    foreach ($results as $result) {
        $text[] = $result[0]->content.' '.$result[2]->content;
    }

    // end
    expect($resultCount)->toBe(2);
    expect($text)->toBe([
        '{{ $one }} {{ $two }}',
        '{{ $two }} {{ $three }}',
    ]);
});

test('get nodes before', function () {
    $template = <<<'BLADE'
    @if {{ $hello }} @endif
BLADE;
    $doc = Document::fromText($template);

    $echo = $doc->getEchoes()->first();
    $before = $doc->getNodesBefore($echo);

    // Returns 3
    $nodeCount = $before->count();

    // end
    expect($nodeCount)->toBe(3);
});

test('get nodes after', function () {
    $template = <<<'BLADE'
    @if {{ $hello }} @endif content {{ $world }}
BLADE;
    $doc = Document::fromText($template);

    $echo = $doc->getEchoes()->first();
    $after = $doc->getNodesAfter($echo);

    // Returns 4
    $nodeCount = $after->count();

    // end
    expect($nodeCount)->toBe(4);
});

test('get all structures', function () {
    $template = <<<'BLADE'
    @if ($varOne)
        @if ($varTwo)
        
        @endif
    @endif
BLADE;
    $doc = Document::fromText($template);

    // Returns 2
    $afterCount = $doc->getAllStructures()->count();

    // end
    expect($afterCount)->toBe(2);
});

test('get direct structures', function () {
    $template = <<<'BLADE'
    @if ($varOne)
        @if ($varTwo)
        
        @endif
    @endif
BLADE;
    $doc = Document::fromText($template);

    // Returns 1
    $count = $doc->getRootStructures()->count();

    // end
    expect($count)->toBe(1);
});

test('get all switch statements', function () {
    $template = <<<'BLADE'
    @switch ($var)
        @case (1)
                @switch ($var)
                    @case (1)
                        @break
                @endswitch
            @break
    @endswitch
BLADE;
    $doc = Document::fromText($template);

    // Returns 2
    $count = $doc->getAllSwitchStatements()->count();

    // end
    expect($count)->toBe(2);
});

test('get direct switch statements', function () {
    $template = <<<'BLADE'
    @switch ($var)
        @case (1)
                @switch ($var)
                    @case (1)
                        @break
                @endswitch
            @break
    @endswitch
BLADE;
    $doc = Document::fromText($template);

    // Returns 1
    $count = $doc->getRootSwitchStatements()->count();

    // end
    expect($count)->toBe(1);
});

test('get all conditions', function () {
    $template = <<<'BLADE'
    @if ($condition)
        @if ($condition)
        
        @endif
    @endif
BLADE;
    $doc = Document::fromText($template);

    // Returns 2
    $count = $doc->getAllConditions()->count();

    // end
    expect($count)->toBe(2);
});

test('get direct conditions', function () {
    $template = <<<'BLADE'
    @if ($condition)
        @if ($condition)
        
        @endif
    @endif
BLADE;
    $doc = Document::fromText($template);

    // Returns 1
    $count = $doc->getRootConditions()->count();

    // end
    expect($count)->toBe(1);
});

test('get all for else', function () {
    $template = <<<'BLADE'
    @forelse ($users as $user)
    
        @forelse ($tasks as $task)
        
        @empty
        
        @endforelse
    
    @empty
    
    @endforelse
BLADE;
    $doc = Document::fromText($template);

    // Returns 2
    $count = $doc->getAllForElse()->count();

    // end
    expect($count)->toBe(2);
});

test('get direct for else', function () {
    $template = <<<'BLADE'
    @forelse ($users as $user)
    
        @forelse ($tasks as $task)
        
        @empty
        
        @endforelse
    
    @empty
    
    @endforelse
BLADE;
    $doc = Document::fromText($template);

    // Returns 1
    $count = $doc->getRootForElse()->count();

    // end
    expect($count)->toBe(1);
});

test('get first error', function () {
    $template = <<<'BLADE'
    Hello, {{ $world
BLADE;
    $doc = Document::fromText($template);

    // Returns '[BLADE_P001001] Unexpected end of input while parsing echo on line 1'
    $message = $doc->getFirstError()->getErrorMessage();

    // end
    expect($message)->toBe('[BLADE_P001001] Unexpected end of input while parsing echo on line 1');
});

test('get first fatal error', function () {
    $template = <<<'BLADE'
    Hello, {{ $world }}
        @verbatim
            {{ $hello 
BLADE;
    $doc = Document::fromText($template);

    // Returns '[BLADE_P003001] Unexpected end of input while parsing verbatim on line 2'
    $message = $doc->getFirstFatalError()->getErrorMessage();

    // end
    expect($message)->toBe('[BLADE_P003001] Unexpected end of input while parsing verbatim on line 2');
});

test('has fatal errors', function () {
    $template = <<<'BLADE'
    Hello, {{ $world }}
        @verbatim
            {{ $hello 
BLADE;
    $doc = Document::fromText($template);

    // Returns true
    $hasFatal = $doc->hasFatalErrors();

    // end
    expect($hasFatal)->toBe(true);
});

test('extract text', function () {
    $template = <<<'BLADE'
@@if ($this) {{ $hello }} @@endif
BLADE;
    $doc = Document::fromText($template);

    // Returns "@if ($this)  @endif"
    $unEscaped = $doc->extractText();

    // Returns "@@if ($this)  @@endif"
    $escaped = $doc->extractText(false);

    // end
    expect($unEscaped)->toBe('@if ($this)  @endif');
    expect($escaped)->toBe('@@if ($this)  @@endif');
});

test('get text', function () {
    $template = <<<'BLADE'
    Hello, {{ $world }}!
BLADE;
    $doc = Document::fromText($template);

    // Returns "Hello,"
    $text = $doc->getText(4, 10);

    // end
    expect($text)->toBe('Hello,');
});

test('get word at offset', function () {
    $doc = Document::fromText('one two three');

    // Returns "two"
    $word = $doc->getWordAtOffset(5);

    // end
    expect($word)->toBe('two');
});

test('get word left at offset', function () {
    $doc = Document::fromText('one two three');

    // Returns "one"
    $word = $doc->getWordLeftAtOffset(5);

    // end
    expect($word)->toBe('one');
});

test('get word right at offset', function () {
    $doc = Document::fromText('one two three');

    // Returns "three"
    $word = $doc->getWordRightAtOffset(5);

    // end
    expect($word)->toBe('three');
});

test('get line excerpt', function () {
    $template = <<<'BLADE'
Line 1
Line 2
Line 3
Line 4
Line 5
Line 6
Line 7
Line 8
BLADE;
    $doc = Document::fromText($template);

    // Returns an array containing the following strings:
    //    2 => "Line 2"
    //    3 => "Line 3"
    //    4 => "Line 4"
    //    5 => "Line 5"
    //    6 => "Line 6"
    $lines = $doc->getLineExcerpt(4, 2);

    // end
    expect($lines)->toBe([
        2 => 'Line 2',
        3 => 'Line 3',
        4 => 'Line 4',
        5 => 'Line 5',
        6 => 'Line 6',
    ]);
});

test('has error on line', function () {
    $template = <<<'BLADE'
    {{ $hello {{-- world --}}
BLADE;
    $doc = Document::fromText($template);

    // Returns true
    $hasError = $doc->hasErrorOnLine(1, ErrorType::UnexpectedCommentEncountered, ConstructContext::Echo);

    // end
    expect($hasError)->toBeTrue();
});

test('with core validators', function () {
    $template = <<<'BLADE'
    {{ $hello {{-- world --}}
BLADE;
    $doc = Document::fromText($template);

    // Returns 19
    $count = $doc->withCoreValidators()
        ->validator()->getValidators()->count();

    // end
    expect($count)->toBe(19);
});

test('with validator', function () {
    $validator = (new class extends AbstractNodeValidator
    {
        function validate(AbstractNode $node): ValidationResult|array|null
        {
            return null;
        }
    });

    $doc = Document::fromText('');

    // Returns 19
    $beforeCount = $doc->withCoreValidators()
        ->validator()->getValidators()->count();

    $doc->withValidator($validator);

    // Returns 20
    $afterCount = $doc->validator()->getValidators()->count();

    // end
    expect($beforeCount)->toBe(19);
    expect($afterCount)->toBe(20);
});

test('validate', function () {
    $template = <<<'BLADE'
    @if ($something)
    
       @endif
BLADE;
    $doc = Document::fromText($template);

    $doc->withValidator(new InconsistentIndentationLevelValidator());
    $doc->validate();

    // Returns:
    // [BLADE_V011] Inconsistent indentation level of 7 for [@endif]; parent [@if] has a level of 4 on line 3
    $message = $doc->getValidationErrors()->first()->getErrorMessage();

    // end
    expect($message)->toBe('[BLADE_V011] Inconsistent indentation level of 7 for [@endif]; parent [@if] has a level of 4 on line 3');
});
