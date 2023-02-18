<?php

namespace Stillat\BladeParser\Tests\Parser;

use Stillat\BladeParser\Nodes\ArgumentContentType;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Tests\ParserTestCase;

class DirectiveParametersTest extends ParserTestCase
{
    public function testDirectiveParametersContainingParenthesis()
    {
        $template = '@php($conditionOne || ($conditionTwo && $conditionThree))';
        $nodes = $this->parseNodes($template);
        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(DirectiveNode::class, $nodes[0]);

        /** @var DirectiveNode $directive */
        $directive = $nodes[0];

        $this->assertNotNull($directive->arguments);

        $this->assertSame('($conditionOne || ($conditionTwo && $conditionThree))', $directive->arguments->content);
        $this->assertSame('$conditionOne || ($conditionTwo && $conditionThree)', $directive->arguments->innerContent);
    }

    public function testDirectiveParametersContainingMismatchedParenthesisInsideStrings()
    {
        $template = '@php($conditionOne || ($conditionTwo && $conditionThree) || "(((((" != ")) (( )) )))")';
        $nodes = $this->parseNodes($template);
        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(DirectiveNode::class, $nodes[0]);

        /** @var DirectiveNode $directive */
        $directive = $nodes[0];

        $this->assertNotNull($directive->arguments);

        $this->assertSame('($conditionOne || ($conditionTwo && $conditionThree) || "(((((" != ")) (( )) )))")', $directive->arguments->content);
        $this->assertSame('$conditionOne || ($conditionTwo && $conditionThree) || "(((((" != ")) (( )) )))"', $directive->arguments->innerContent);
    }

    public function testDirectiveParametersContainingPhpLineComments()
    {
        $template = <<<'EOT'
@isset(
        $records // @isset())2
        )
// $records is defined and is not null...
@endisset
EOT;

        $literalContent = <<<'LITERAL'

// $records is defined and is not null...

LITERAL;

        $nodes = $this->parseNodes($template);
        $this->assertCount(3, $nodes);
        $this->assertInstanceOf(DirectiveNode::class, $nodes[0]);

        $this->assertLiteralContent($nodes[1], $literalContent);

        $this->assertInstanceOf(DirectiveNode::class, $nodes[2]);

        $isset = $nodes[0];
        $this->assertNotNull($isset->arguments);
        $this->assertSame('isset', $isset->content);

        $argsOuterContent = <<<'OUTER'
(
        $records // @isset())2
        )
OUTER;
        $argsInnerContent = <<<'INNER'

        $records // @isset())2
        
INNER;

        $this->assertSame($argsOuterContent, $isset->arguments->content);
        $this->assertSame($argsInnerContent, $isset->arguments->innerContent);

        /** @var DirectiveNode $endIsset */
        $endIsset = $nodes[2];
        $this->assertNull($endIsset->arguments);
        $this->assertSame('endisset', $endIsset->content);
    }

    public function testDirectivesContainingMultilinePhpComments()
    {
        $template = <<<'EOT'
@isset(
        $records /* @isset())2
        @isset(
            $records /* @isset())2
            )
    // $records is defined and is not null...
    @endisset
    */
        a)b
// $records is defined and is not null...
@endisset
EOT;
        $nodes = $this->parseNodes($template);
        $this->assertCount(3, $nodes);
        $this->assertInstanceOf(DirectiveNode::class, $nodes[0]);

        $literalContent = <<<'LITERAL'
b
// $records is defined and is not null...

LITERAL;

        $this->assertLiteralContent($nodes[1], $literalContent);
        $this->assertInstanceOf(DirectiveNode::class, $nodes[2]);

        $isset = $nodes[0];
        $this->assertNotNull($isset->arguments);
        $this->assertSame('isset', $isset->content);

        $argsOuterContent = <<<'OUTER'
(
        $records /* @isset())2
        @isset(
            $records /* @isset())2
            )
    // $records is defined and is not null...
    @endisset
    */
        a)
OUTER;
        $argsInnerContent = <<<'INNER'

        $records /* @isset())2
        @isset(
            $records /* @isset())2
            )
    // $records is defined and is not null...
    @endisset
    */
        a
INNER;

        $this->assertSame($argsOuterContent, $isset->arguments->content);
        $this->assertSame($argsInnerContent, $isset->arguments->innerContent);

        /** @var DirectiveNode $endIsset */
        $endIsset = $nodes[2];
        $this->assertNull($endIsset->arguments);
        $this->assertSame('endisset', $endIsset->content);
    }

    public function testArgumentsWithPhpContentType()
    {
        $this->registerDirective('directive');

        $template = <<<'EOT'
@directive($that == $this)
EOT;
        $nodes = $this->parseNodes($template);
        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(DirectiveNode::class, $nodes[0]);

        /** @var DirectiveNode $directive */
        $directive = $nodes[0];
        $this->assertSame('directive', $directive->content);
        $this->assertNotNull($directive->arguments);

        $this->assertSame('($that == $this)', $directive->arguments->content);
        $this->assertSame('$that == $this', $directive->arguments->innerContent);
        $this->assertSame(ArgumentContentType::Php, $directive->arguments->contentType);
    }

    public function testArgumentsWithJsonContentType()
    {
        $this->registerDirective('directive');

        $template = <<<'EOT'
    @directive({
        "key": "value",
        "key2": "value2"
    })
    EOT;
        $nodes = $this->parseNodes($template);
        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(DirectiveNode::class, $nodes[0]);

        /** @var DirectiveNode $directive */
        $directive = $nodes[0];
        $this->assertSame('directive', $directive->content);
        $this->assertNotNull($directive->arguments);

        $outerContent = <<<'OUTER'
    ({
        "key": "value",
        "key2": "value2"
    })
    OUTER;

        $innerContent = <<<'INNER'
    {
        "key": "value",
        "key2": "value2"
    }
    INNER;

        $this->assertSame($outerContent, $directive->arguments->content);
        $this->assertSame($innerContent, $directive->arguments->innerContent);
        $this->assertSame(ArgumentContentType::Json, $directive->arguments->contentType);
    }

    public function testJsonArgsDoNotHaveStringValues()
    {
        $directive = $this->getDocument(' @lang({"something": true}) ')->findDirectiveByName('lang');
        $this->assertFalse($directive->arguments->hasStringValue());
        $this->assertSame('', $directive->arguments->getStringValue());
    }

    public function testStringArgsHaveStringValues()
    {
        $directive = $this->getDocument(' @lang("something") ')->findDirectiveByName('lang');
        $this->assertTrue($directive->arguments->hasStringValue());
        $this->assertSame('something', $directive->arguments->getStringValue());

        $directive = $this->getDocument(" @lang('something') ")->findDirectiveByName('lang');
        $this->assertTrue($directive->arguments->hasStringValue());
        $this->assertSame('something', $directive->arguments->getStringValue());
    }
}
