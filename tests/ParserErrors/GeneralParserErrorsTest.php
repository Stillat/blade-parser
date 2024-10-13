<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Errors\BladeError;
use Stillat\BladeParser\Errors\ConstructContext;
use Stillat\BladeParser\Errors\ErrorType;
use Stillat\BladeParser\Errors\Exceptions\CompilationException;

test('unmatched verbatim triggers error', function () {
    $template = <<<'EOT'

    @verbatim
        Some stuff
EOT;
    $result = $this->compiler->compileString($template);
    $this->assertHasErrorOnLine(2, ErrorType::UnexpectedEndOfInput, ConstructContext::Verbatim);
    expect($result)->toBe($template);
});

test('unmatched verbatim triggers exception', function () {
    $this->expectException(CompilationException::class);

    $template = <<<'EOT'

    @verbatim
        Some stuff
EOT;
    $this->compiler->setFailOnParserErrors(true);
    $this->compiler->compileString($template);
});

test('unmatched php triggers error', function () {
    $template = <<<'EOT'

    @php
        Some stuff
EOT;
    $result = $this->compiler->compileString($template);
    $this->assertHasErrorOnLine(2, ErrorType::UnexpectedEndOfInput, ConstructContext::BladePhpBlock);
    expect($result)->toBe($template);
});

test('unmatched php does not trigger exceptions', function () {
    $template = <<<'EOT'

    @php
        Some stuff
EOT;
    $this->compiler->setFailOnParserErrors(true);
    $result = $this->compiler->compileString($template);
    $this->assertHasErrorOnLine(2, ErrorType::UnexpectedEndOfInput, ConstructContext::BladePhpBlock);
    expect($result)->toBe($template);
});

test('unmatched php triggers exceptions when in strict mode', function () {
    $this->expectException(CompilationException::class);

    $template = <<<'EOT'

    @php
        Some stuff
EOT;
    $this->compiler->setFailOnParserErrors(true);
    $this->compiler->setParserErrorsIsStrict(true);
    $this->compiler->compileString($template);
});

test('unclosed echo triggers error', function () {
    $template = <<<'EOT'

    {{ $value
        Some stuff
EOT;
    $result = $this->compiler->compileString($template);
    $this->assertHasErrorOnLine(2, ErrorType::UnexpectedEndOfInput, ConstructContext::Echo);
    expect($result)->toBe($template);
});

test('unclosed echo triggers exception', function () {
    $this->expectException(CompilationException::class);

    $template = <<<'EOT'

    {{ $value
        Some stuff
EOT;
    $this->compiler->setFailOnParserErrors(true);
    $this->compiler->compileString($template);
});

test('unclosed raw echo triggers error', function () {
    $template = <<<'EOT'

    {!! $value
        Some stuff
EOT;
    $result = $this->compiler->compileString($template);
    $this->assertHasErrorOnLine(2, ErrorType::UnexpectedEndOfInput, ConstructContext::RawEcho);
    expect($result)->toBe($template);
});

test('unclosed raw echo triggers exception', function () {
    $this->expectException(CompilationException::class);

    $template = <<<'EOT'

    {!! $value
        Some stuff
EOT;
    $this->compiler->setFailOnParserErrors(true);
    $this->compiler->compileString($template);
});

test('unclosed triple echo triggers error', function () {
    $template = <<<'EOT'

    {{{ $value
        Some stuff
EOT;
    $result = $this->compiler->compileString($template);
    $this->assertHasErrorOnLine(2, ErrorType::UnexpectedEndOfInput, ConstructContext::TripleEcho);
    expect($result)->toBe($template);
});

test('unclosed triple echo triggers exception', function () {
    $this->expectException(CompilationException::class);
    $template = <<<'EOT'

    {{{ $value
        Some stuff
EOT;
    $this->compiler->setFailOnParserErrors(true);
    $this->compiler->compileString($template);
});

test('unclosed comment triggers error', function () {
    $template = <<<'EOT'

    {{-- $value
        Some stuff
EOT;
    $result = $this->compiler->compileString($template);
    $this->assertHasErrorOnLine(2, ErrorType::UnexpectedEndOfInput, ConstructContext::Comment);
    expect($result)->toBe($template);
});

test('unclosed comment triggers exception', function () {
    $this->expectException(CompilationException::class);
    $template = <<<'EOT'

    {{-- $value
        Some stuff
EOT;
    $this->compiler->setFailOnParserErrors(true);
    $this->compiler->compileString($template);
});

test('unclosed component tag triggers error', function () {
    $template = <<<'EOT'

    <x-profile message="the-message"
        Some stuff
EOT;
    $result = $this->compiler->compileString($template);
    $this->assertHasErrorOnLine(2, ErrorType::UnexpectedEndOfInput, ConstructContext::ComponentTag);
    expect($result)->toBe($template);
});

test('unclosed component tag triggers exception', function () {
    $this->expectException(CompilationException::class);

    $template = <<<'EOT'

    <x-profile message="the-message"
        Some stuff
EOT;
    $this->compiler->setFailOnParserErrors(true);
    $this->compiler->compileString($template);
});

test('echos encountering echos', function () {
    $template = <<<'EOT'
{{ $variable {{ $hello }}
EOT;
    $result = $this->compiler->compileString($template);
    expect($result)->toBe('{{ $variable <?php echo e($hello); ?>');

    $this->assertHasErrorOnLine(1, ErrorType::UnexpectedEchoEncountered, ConstructContext::Echo);
});

test('unexpected echo and end of content', function () {
    $document = <<<'EOT'

{{ $variable {{ $hello
EOT;
    $result = $this->compiler->compileString($document);
    expect($result)->toBe($document);

    $this->assertHasErrorOnLine(2, ErrorType::UnexpectedEchoEncountered, ConstructContext::Echo);
    $this->assertHasErrorOnLine(2, ErrorType::UnexpectedEndOfInput, ConstructContext::Echo);
});

test('unexpected raw echo and end of content', function () {
    $document = <<<'EOT'

{!! $variable {!! $hello
EOT;
    $result = $this->compiler->compileString($document);
    expect($result)->toBe($document);

    $this->assertHasErrorOnLine(2, ErrorType::UnexpectedRawEchoEncountered, ConstructContext::RawEcho);
    $this->assertHasErrorOnLine(2, ErrorType::UnexpectedEndOfInput, ConstructContext::RawEcho);
});

test('unexpected triple echo and end of content', function () {
    $document = <<<'EOT'

{{{ $variable {{{ $hello
EOT;
    $result = $this->compiler->compileString($document);
    expect($result)->toBe($document);

    $this->assertHasErrorOnLine(2, ErrorType::UnexpectedTripleEchoEncountered, ConstructContext::TripleEcho);
    $this->assertHasErrorOnLine(2, ErrorType::UnexpectedEndOfInput, ConstructContext::TripleEcho);
});

test('produced error codes', function () {
    $template = <<<'EOT'

    <x-profile message="the-message"
        Some stuff
        {{ $var
        {{-- A comment
        {{{ $anotherVar
        {!! $yetAnotherVar
EOT;
    $errorCodes = $this->getDocument($template)->getErrors()->map(fn (BladeError $e) => $e->getErrorCode())->all();

    $expected = [
        'BLADE_P006001',
        'BLADE_P001005',
        'BLADE_P002001',
        'BLADE_P008003',
        'BLADE_P007001',
    ];

    expect($errorCodes)->toBe($expected);
});

test('errors are created from mixed unclosed structures', function () {
    $template = <<<'EOT'
{{ $var1 {!! $varTwo {{{ $varThree {{ $varFour }} {{ $varFive
EOT;
    $result = $this->compiler->compileString($template);
    $expected = <<<'EXPECTED'
{{ $var1 {!! $varTwo {{{ $varThree <?php echo e($varFour); ?> {{ $varFive
EXPECTED;
    expect($expected)->toBe($result);

    $this->assertHasErrorOnLine(1, ErrorType::UnexpectedRawEchoEncountered, ConstructContext::Echo);
    $this->assertHasErrorOnLine(1, ErrorType::UnexpectedTripleEchoEncountered, ConstructContext::RawEcho);
    $this->assertHasErrorOnLine(1, ErrorType::UnexpectedEchoEncountered, ConstructContext::TripleEcho);
    $this->assertHasErrorOnLine(1, ErrorType::UnexpectedEndOfInput, ConstructContext::Echo);
});

test('unexpected components are detected', function () {
    $template = <<<'EOT'

{{ $hello <x-alert message 
EOT;
    $result = $this->compiler->compileString($template);
    expect($result)->toBe($template);

    $this->assertHasErrorOnLine(2, ErrorType::UnexpectedComponentTagEncountered, ConstructContext::Echo);
    $this->assertHasErrorOnLine(2, ErrorType::UnexpectedEndOfInput, ConstructContext::ComponentTag);
});

test('unexpected colon components are detected', function () {
    $template = <<<'EOT'

{{ $hello <x:alert message 
EOT;
    $result = $this->compiler->compileString($template);
    expect($result)->toBe($template);

    $this->assertHasErrorOnLine(2, ErrorType::UnexpectedNamespacedComponentTagEncountered, ConstructContext::Echo);
    $this->assertHasErrorOnLine(2, ErrorType::UnexpectedEndOfInput, ConstructContext::ComponentTag);
});

test('unexpected closing component tags are detected', function () {
    $template = <<<'EOT'

{{ $hello </x-alert 
EOT;
    $result = $this->compiler->compileString($template);
    expect($result)->toBe($template);

    $this->assertHasErrorOnLine(2, ErrorType::UnexpectedComponentClosingTagEncountered, ConstructContext::Echo);
    $this->assertHasErrorOnLine(2, ErrorType::UnexpectedEndOfInput, ConstructContext::ComponentTag);
});

test('unexpected colon closing component tags are detected', function () {
    $template = <<<'EOT'

{{ $hello </x:alert 
EOT;
    $result = $this->compiler->compileString($template);
    expect($result)->toBe($template);

    $this->assertHasErrorOnLine(2, ErrorType::UnexpectedNamespacedComponentClosingTagEncountered, ConstructContext::Echo);
    $this->assertHasErrorOnLine(2, ErrorType::UnexpectedEndOfInput, ConstructContext::ComponentTag);
});

test('unexpected blade comments are detected', function () {
    $template = <<<'EOT'

    {{ $hello
        {{-- I am a comment.
EOT;
    $result = $this->compiler->compileString($template);
    expect($result)->toBe($template);

    $this->assertHasErrorOnLine(2, ErrorType::UnexpectedCommentEncountered, ConstructContext::Echo);
    $this->assertHasErrorOnLine(3, ErrorType::UnexpectedEndOfInput, ConstructContext::Comment);
});

test('unexpected short php tags are detected', function () {
    $template = <<<'EOT'

    {{ $hello
        <?= $letsGo
EOT;
    $result = $this->compiler->compileString($template);
    expect($result)->toBe($template);

    $this->assertHasErrorOnLine(2, ErrorType::UnexpectedPhpShortOpen, ConstructContext::Echo);
    $this->assertHasErrorOnLine(3, ErrorType::UnexpectedEndOfInput, ConstructContext::PhpShortOpen);
});

test('unexpected php closing tags are detected', function () {
    $template = <<<'EOT'

    {{ $hello
        ?>
EOT;
    $result = $this->compiler->compileString($template);
    expect($result)->toBe($template);

    $this->assertHasErrorOnLine(2, ErrorType::UnexpectedPhpClosingTag, ConstructContext::Echo);
});
