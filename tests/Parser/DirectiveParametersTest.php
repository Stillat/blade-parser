<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Nodes\ArgumentContentType;
use Stillat\BladeParser\Nodes\DirectiveNode;

test('directive parameters containing parenthesis', function () {
    $template = '@php($conditionOne || ($conditionTwo && $conditionThree))';
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(1);
    expect($nodes[0])->toBeInstanceOf(DirectiveNode::class);

    /** @var DirectiveNode $directive */
    $directive = $nodes[0];

    expect($directive->arguments)->not->toBeNull();

    expect($directive->arguments->content)->toBe('($conditionOne || ($conditionTwo && $conditionThree))');
    expect($directive->arguments->innerContent)->toBe('$conditionOne || ($conditionTwo && $conditionThree)');
});

test('directive parameters containing mismatched parenthesis inside strings', function () {
    $template = '@php($conditionOne || ($conditionTwo && $conditionThree) || "(((((" != ")) (( )) )))")';
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(1);
    expect($nodes[0])->toBeInstanceOf(DirectiveNode::class);

    /** @var DirectiveNode $directive */
    $directive = $nodes[0];

    expect($directive->arguments)->not->toBeNull();

    expect($directive->arguments->content)->toBe('($conditionOne || ($conditionTwo && $conditionThree) || "(((((" != ")) (( )) )))")');
    expect($directive->arguments->innerContent)->toBe('$conditionOne || ($conditionTwo && $conditionThree) || "(((((" != ")) (( )) )))"');
});

test('directive parameters containing php line comments', function () {
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
    expect($nodes)->toHaveCount(3);
    expect($nodes[0])->toBeInstanceOf(DirectiveNode::class);

    $this->assertLiteralContent($nodes[1], $literalContent);

    expect($nodes[2])->toBeInstanceOf(DirectiveNode::class);

    $isset = $nodes[0];
    expect($isset->arguments)->not->toBeNull();
    expect($isset->content)->toBe('isset');

    $argsOuterContent = <<<'OUTER'
(
        $records // @isset())2
        )
OUTER;
    $argsInnerContent = <<<'INNER'

        $records // @isset())2
        
INNER;

    expect($isset->arguments->content)->toBe($argsOuterContent);
    expect($isset->arguments->innerContent)->toBe($argsInnerContent);

    /** @var DirectiveNode $endIsset */
    $endIsset = $nodes[2];
    expect($endIsset->arguments)->toBeNull();
    expect($endIsset->content)->toBe('endisset');
});

test('directives containing multiline php comments', function () {
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
    expect($nodes)->toHaveCount(3);
    expect($nodes[0])->toBeInstanceOf(DirectiveNode::class);

    $literalContent = <<<'LITERAL'
b
// $records is defined and is not null...

LITERAL;

    $this->assertLiteralContent($nodes[1], $literalContent);
    expect($nodes[2])->toBeInstanceOf(DirectiveNode::class);

    $isset = $nodes[0];
    expect($isset->arguments)->not->toBeNull();
    expect($isset->content)->toBe('isset');

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

    expect($isset->arguments->content)->toBe($argsOuterContent);
    expect($isset->arguments->innerContent)->toBe($argsInnerContent);

    /** @var DirectiveNode $endIsset */
    $endIsset = $nodes[2];
    expect($endIsset->arguments)->toBeNull();
    expect($endIsset->content)->toBe('endisset');
});

test('arguments with php content type', function () {
    $this->registerDirective('directive');

    $template = <<<'EOT'
@directive($that == $this)
EOT;
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(1);
    expect($nodes[0])->toBeInstanceOf(DirectiveNode::class);

    /** @var DirectiveNode $directive */
    $directive = $nodes[0];
    expect($directive->content)->toBe('directive');
    expect($directive->arguments)->not->toBeNull();

    expect($directive->arguments->content)->toBe('($that == $this)');
    expect($directive->arguments->innerContent)->toBe('$that == $this');
    expect($directive->arguments->contentType)->toBe(ArgumentContentType::Php);
});

test('arguments with json content type', function () {
    $this->registerDirective('directive');

    $template = <<<'EOT'
    @directive({
        "key": "value",
        "key2": "value2"
    })
    EOT;
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(1);
    expect($nodes[0])->toBeInstanceOf(DirectiveNode::class);

    /** @var DirectiveNode $directive */
    $directive = $nodes[0];
    expect($directive->content)->toBe('directive');
    expect($directive->arguments)->not->toBeNull();

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

    expect($directive->arguments->content)->toBe($outerContent);
    expect($directive->arguments->innerContent)->toBe($innerContent);
    expect($directive->arguments->contentType)->toBe(ArgumentContentType::Json);
});

test('json args do not have string values', function () {
    $directive = $this->getDocument(' @lang({"something": true}) ')->findDirectiveByName('lang');
    expect($directive->arguments->hasStringValue())->toBeFalse();
    expect($directive->arguments->getStringValue())->toBe('');
});

test('string args have string values', function () {
    $directive = $this->getDocument(' @lang("something") ')->findDirectiveByName('lang');
    expect($directive->arguments->hasStringValue())->toBeTrue();
    expect($directive->arguments->getStringValue())->toBe('something');

    $directive = $this->getDocument(" @lang('something') ")->findDirectiveByName('lang');
    expect($directive->arguments->hasStringValue())->toBeTrue();
    expect($directive->arguments->getStringValue())->toBe('something');
});
