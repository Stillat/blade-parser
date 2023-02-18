<?php

namespace Stillat\BladeParser\Tests\Examples;

use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Errors\ConstructContext;
use Stillat\BladeParser\Errors\ErrorType;
use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\EchoNode;
use Stillat\BladeParser\Nodes\LiteralNode;
use Stillat\BladeParser\Tests\ParserTestCase;
use Stillat\BladeParser\Validation\AbstractNodeValidator;
use Stillat\BladeParser\Validation\ValidationResult;
use Stillat\BladeParser\Validation\Validators\InconsistentIndentationLevelValidator;

class DocumentExampleTest extends ParserTestCase
{
    public function testRemoveNode()
    {
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

        $this->assertSame($expected, $result);
    }

    public function testGetEchoes()
    {
        $template = <<<'BLADE'
    {{ $variableOne }}
    {{{ $variableTwo }}}
    {!! $variableThree !!}
BLADE;
        $doc = Document::fromText($template);

        // Returns 3
        $echoCount = $doc->getEchoes()->count();

        // end
        $this->assertSame(3, $echoCount);
    }

    public function testGetPhpTags()
    {
        $template = <<<'BLADE'
    <?php $count = 1; ?>
    
        The count is: <?= $count ?>
BLADE;
        $doc = Document::fromText($template);

        // Returns 2
        $phpTagCount = $doc->getPhpTags()->count();

        // end
        $this->assertSame(2, $phpTagCount);
    }

    public function testGetPhpBlocks()
    {
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
        $this->assertSame(2, $phpBlockCount);
    }

    public function testGetVerbatimBlocks()
    {
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
        $this->assertSame(2, $verbatimCount);
    }

    public function testGetLiterals()
    {
        $template = <<<'BLADE'
    One {{ $variable }} Two {{ $variable }}

BLADE;
        $doc = Document::fromText($template);

        // Returns 3
        $literalCount = $doc->getLiterals()->count();

        // end
        $this->assertSame(3, $literalCount);
    }

    public function testGetDirectives()
    {
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
        $this->assertSame(4, $directiveCount);
    }

    public function testFindDirectiveByName()
    {
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
        $this->assertSame('another/file', $include);
    }

    public function testFindDirectivesByName()
    {
        $template = <<<'BLADE'
    @include ('file/one') @if ($something) @endif
        @include ('file/two')
            @include
BLADE;
        $doc = Document::fromText($template);

        // Returns 3
        $includeCount = $doc->findDirectivesByName('include')->count();

        // end
        $this->assertSame(3, $includeCount);
    }

    public function testHasDirective()
    {
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
        $this->assertTrue($hasExtends);
        $this->assertFalse($hasCan);
    }

    public function testGetComments()
    {
        $template = <<<'BLADE'
    {{-- One --}}
        {{-- Two --}}
BLADE;
        $doc = Document::fromText($template);

        // Returns 2
        $commentCount = $doc->getComments()->count();

        // end
        $this->assertSame(2, $commentCount);
    }

    public function testGetComponents()
    {
        $template = <<<'BLADE'
    <x-profile :$user />

    <x-alert></x-alert>
BLADE;
        $doc = Document::fromText($template);

        // Returns 3
        $componentCount = $doc->getComponents()->count();

        // end
        $this->assertSame(3, $componentCount);
    }

    public function testGetOpeningComponentTags()
    {
        $template = <<<'BLADE'
    <x-profile :$user />

    <x-alert></x-alert>
BLADE;
        $doc = Document::fromText($template);

        // Returns 2
        $componentCount = $doc->getOpeningComponentTags()->count();

        // end
        $this->assertSame(2, $componentCount);
    }

    public function testFindComponentsByTagName()
    {
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
        $this->assertSame(4, $componentCount);
    }

    public function testFindComponentByTagName()
    {
        $template = <<<'BLADE'
    <x-message content="message" />
    <x-profile />
BLADE;
        $doc = Document::fromText($template);

        $component = $doc->findComponentByTagName('message');

        // Returns 'message content="message" '
        $innerContent = $component->innerContent;

        // end
        $this->assertSame('message content="message" ', $innerContent);
    }

    public function testAllOfType()
    {
        $template = <<<'BLADE'

    {{ $hello }}

BLADE;
        $doc = Document::fromText($template);

        // Returns 2
        $count = $doc->allOfType(LiteralNode::class)->count();

        // end
        $this->assertSame(2, $count);
    }

    public function testAllNotOfType()
    {
        $template = <<<'BLADE'

    {{ $hello }}

BLADE;
        $doc = Document::fromText($template);

        // Returns 2
        $count = $doc->allOfType(EchoNode::class)->count();

        // end
        $this->assertSame(1, $count);
    }

    public function testFirstOfType()
    {
        $template = <<<'BLADE'
    @if
    @can
    @include
BLADE;
        $doc = Document::fromText($template);

        // Returns 'if'
        $content = $doc->firstOfType(DirectiveNode::class)->content;

        // end
        $this->assertSame('if', $content);
    }

    public function testLastOfType()
    {
        $template = <<<'BLADE'
    @if
    @can
    @include
BLADE;
        $doc = Document::fromText($template);

        // Returns 'include'
        $content = $doc->lastOfType(DirectiveNode::class)->content;

        // end
        $this->assertSame('include', $content);
    }

    public function testFindNodePattern()
    {
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
        $this->assertSame(2, $resultCount);
        $this->assertSame([
            '{{ $one }} {{ $two }}',
            '{{ $two }} {{ $three }}',
        ], $text);
    }

    public function testGetNodesBefore()
    {
        $template = <<<'BLADE'
    @if {{ $hello }} @endif
BLADE;
        $doc = Document::fromText($template);

        $echo = $doc->getEchoes()->first();
        $before = $doc->getNodesBefore($echo);

        // Returns 3
        $nodeCount = $before->count();

        // end
        $this->assertSame(3, $nodeCount);
    }

    public function testGetNodesAfter()
    {
        $template = <<<'BLADE'
    @if {{ $hello }} @endif content {{ $world }}
BLADE;
        $doc = Document::fromText($template);

        $echo = $doc->getEchoes()->first();
        $after = $doc->getNodesAfter($echo);

        // Returns 4
        $nodeCount = $after->count();

        // end
        $this->assertSame(4, $nodeCount);
    }

    public function testGetAllStructures()
    {
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
        $this->assertSame(2, $afterCount);
    }

    public function testGetDirectStructures()
    {
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
        $this->assertSame(1, $count);
    }

    public function testGetAllSwitchStatements()
    {
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
        $this->assertSame(2, $count);
    }

    public function testGetDirectSwitchStatements()
    {
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
        $this->assertSame(1, $count);
    }

    public function testGetAllConditions()
    {
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
        $this->assertSame(2, $count);
    }

    public function testGetDirectConditions()
    {
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
        $this->assertSame(1, $count);
    }

    public function testGetAllForElse()
    {
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
        $this->assertSame(2, $count);
    }

    public function testGetDirectForElse()
    {
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
        $this->assertSame(1, $count);
    }

    public function testGetFirstError()
    {
        $template = <<<'BLADE'
    Hello, {{ $world
BLADE;
        $doc = Document::fromText($template);

        // Returns '[BLADE_P001001] Unexpected end of input while parsing echo on line 1'
        $message = $doc->getFirstError()->getErrorMessage();

        // end
        $this->assertSame('[BLADE_P001001] Unexpected end of input while parsing echo on line 1', $message);
    }

    public function testGetFirstFatalError()
    {
        $template = <<<'BLADE'
    Hello, {{ $world }}
        @verbatim
            {{ $hello 
BLADE;
        $doc = Document::fromText($template);

        // Returns '[BLADE_P003001] Unexpected end of input while parsing verbatim on line 2'
        $message = $doc->getFirstFatalError()->getErrorMessage();

        // end
        $this->assertSame('[BLADE_P003001] Unexpected end of input while parsing verbatim on line 2', $message);
    }

    public function testHasFatalErrors()
    {
        $template = <<<'BLADE'
    Hello, {{ $world }}
        @verbatim
            {{ $hello 
BLADE;
        $doc = Document::fromText($template);

        // Returns true
        $hasFatal = $doc->hasFatalErrors();

        // end
        $this->assertSame(true, $hasFatal);
    }

    public function testExtractText()
    {
        $template = <<<'BLADE'
@@if ($this) {{ $hello }} @@endif
BLADE;
        $doc = Document::fromText($template);

        // Returns "@if ($this)  @endif"
        $unEscaped = $doc->extractText();

        // Returns "@@if ($this)  @@endif"
        $escaped = $doc->extractText(false);

        // end
        $this->assertSame('@if ($this)  @endif', $unEscaped);
        $this->assertSame('@@if ($this)  @@endif', $escaped);
    }

    public function testGetText()
    {
        $template = <<<'BLADE'
    Hello, {{ $world }}!
BLADE;
        $doc = Document::fromText($template);

        // Returns "Hello,"
        $text = $doc->getText(4, 10);

        // end
        $this->assertSame('Hello,', $text);
    }

    public function testGetWordAtOffset()
    {
        $doc = Document::fromText('one two three');

        // Returns "two"
        $word = $doc->getWordAtOffset(5);

        // end
        $this->assertSame('two', $word);
    }

    public function testGetWordLeftAtOffset()
    {
        $doc = Document::fromText('one two three');

        // Returns "one"
        $word = $doc->getWordLeftAtOffset(5);

        // end
        $this->assertSame('one', $word);
    }

    public function testGetWordRightAtOffset()
    {
        $doc = Document::fromText('one two three');

        // Returns "three"
        $word = $doc->getWordRightAtOffset(5);

        // end
        $this->assertSame('three', $word);
    }

    public function testGetLineExcerpt()
    {
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
        $this->assertSame([
            2 => 'Line 2',
            3 => 'Line 3',
            4 => 'Line 4',
            5 => 'Line 5',
            6 => 'Line 6',
        ], $lines);
    }

    public function testHasErrorOnLine()
    {
        $template = <<<'BLADE'
    {{ $hello {{-- world --}}
BLADE;
        $doc = Document::fromText($template);

        // Returns true
        $hasError = $doc->hasErrorOnLine(1, ErrorType::UnexpectedCommentEncountered, ConstructContext::Echo);

        // end
        $this->assertTrue($hasError);
    }

    public function testWithCoreValidators()
    {
        $template = <<<'BLADE'
    {{ $hello {{-- world --}}
BLADE;
        $doc = Document::fromText($template);

        // Returns 19
        $count = $doc->withCoreValidators()
            ->validator()->getValidators()->count();

        // end
        $this->assertSame(19, $count);
    }

    public function testWithValidator()
    {
        $validator = (new class extends AbstractNodeValidator
        {
            public function validate(AbstractNode $node): ValidationResult|array|null
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
        $this->assertSame(19, $beforeCount);
        $this->assertSame(20, $afterCount);
    }

    public function testValidate()
    {
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
        $this->assertSame('[BLADE_V011] Inconsistent indentation level of 7 for [@endif]; parent [@if] has a level of 4 on line 3', $message);
    }
}
