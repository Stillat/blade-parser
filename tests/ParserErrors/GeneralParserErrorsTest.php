<?php

namespace Stillat\BladeParser\Tests\ParserErrors;

use Stillat\BladeParser\Errors\BladeError;
use Stillat\BladeParser\Errors\ConstructContext;
use Stillat\BladeParser\Errors\ErrorType;
use Stillat\BladeParser\Errors\Exceptions\CompilationException;
use Stillat\BladeParser\Tests\ParserTestCase;

class GeneralParserErrorsTest extends ParserTestCase
{
    public function testUnmatchedVerbatimTriggersError()
    {
        $template = <<<'EOT'

    @verbatim
        Some stuff
EOT;
        $result = $this->compiler->compileString($template);
        $this->assertHasErrorOnLine(2, ErrorType::UnexpectedEndOfInput, ConstructContext::Verbatim);
        $this->assertSame($template, $result);
    }

    public function testUnmatchedVerbatimTriggersException()
    {
        $this->expectException(CompilationException::class);

        $template = <<<'EOT'

    @verbatim
        Some stuff
EOT;
        $this->compiler->setFailOnParserErrors(true);
        $this->compiler->compileString($template);
    }

    public function testUnmatchedPhpTriggersError()
    {
        $template = <<<'EOT'

    @php
        Some stuff
EOT;
        $result = $this->compiler->compileString($template);
        $this->assertHasErrorOnLine(2, ErrorType::UnexpectedEndOfInput, ConstructContext::BladePhpBlock);
        $this->assertSame($template, $result);
    }

    public function testUnmatchedPhpDoesNotTriggerExceptions()
    {
        $template = <<<'EOT'

    @php
        Some stuff
EOT;
        $this->compiler->setFailOnParserErrors(true);
        $result = $this->compiler->compileString($template);
        $this->assertHasErrorOnLine(2, ErrorType::UnexpectedEndOfInput, ConstructContext::BladePhpBlock);
        $this->assertSame($template, $result);
    }

    public function testUnmatchedPhpTriggersExceptionsWhenInStrictMode()
    {
        $this->expectException(CompilationException::class);

        $template = <<<'EOT'

    @php
        Some stuff
EOT;
        $this->compiler->setFailOnParserErrors(true);
        $this->compiler->setParserErrorsIsStrict(true);
        $this->compiler->compileString($template);
    }

    public function testUnclosedEchoTriggersError()
    {
        $template = <<<'EOT'

    {{ $value
        Some stuff
EOT;
        $result = $this->compiler->compileString($template);
        $this->assertHasErrorOnLine(2, ErrorType::UnexpectedEndOfInput, ConstructContext::Echo);
        $this->assertSame($template, $result);
    }

    public function testUnclosedEchoTriggersException()
    {
        $this->expectException(CompilationException::class);

        $template = <<<'EOT'

    {{ $value
        Some stuff
EOT;
        $this->compiler->setFailOnParserErrors(true);
        $this->compiler->compileString($template);
    }

    public function testUnclosedRawEchoTriggersError()
    {
        $template = <<<'EOT'

    {!! $value
        Some stuff
EOT;
        $result = $this->compiler->compileString($template);
        $this->assertHasErrorOnLine(2, ErrorType::UnexpectedEndOfInput, ConstructContext::RawEcho);
        $this->assertSame($template, $result);
    }

    public function testUnclosedRawEchoTriggersException()
    {
        $this->expectException(CompilationException::class);

        $template = <<<'EOT'

    {!! $value
        Some stuff
EOT;
        $this->compiler->setFailOnParserErrors(true);
        $this->compiler->compileString($template);
    }

    public function testUnclosedTripleEchoTriggersError()
    {
        $template = <<<'EOT'

    {{{ $value
        Some stuff
EOT;
        $result = $this->compiler->compileString($template);
        $this->assertHasErrorOnLine(2, ErrorType::UnexpectedEndOfInput, ConstructContext::TripleEcho);
        $this->assertSame($template, $result);
    }

    public function testUnclosedTripleEchoTriggersException()
    {
        $this->expectException(CompilationException::class);
        $template = <<<'EOT'

    {{{ $value
        Some stuff
EOT;
        $this->compiler->setFailOnParserErrors(true);
        $this->compiler->compileString($template);
    }

    public function testUnclosedCommentTriggersError()
    {
        $template = <<<'EOT'

    {{-- $value
        Some stuff
EOT;
        $result = $this->compiler->compileString($template);
        $this->assertHasErrorOnLine(2, ErrorType::UnexpectedEndOfInput, ConstructContext::Comment);
        $this->assertSame($template, $result);
    }

    public function testUnclosedCommentTriggersException()
    {
        $this->expectException(CompilationException::class);
        $template = <<<'EOT'

    {{-- $value
        Some stuff
EOT;
        $this->compiler->setFailOnParserErrors(true);
        $this->compiler->compileString($template);
    }

    public function testUnclosedComponentTagTriggersError()
    {
        $template = <<<'EOT'

    <x-profile message="the-message"
        Some stuff
EOT;
        $result = $this->compiler->compileString($template);
        $this->assertHasErrorOnLine(2, ErrorType::UnexpectedEndOfInput, ConstructContext::ComponentTag);
        $this->assertSame($template, $result);
    }

    public function testUnclosedComponentTagTriggersException()
    {
        $this->expectException(CompilationException::class);

        $template = <<<'EOT'

    <x-profile message="the-message"
        Some stuff
EOT;
        $this->compiler->setFailOnParserErrors(true);
        $this->compiler->compileString($template);
    }

    public function testEchosEncounteringEchos()
    {
        $template = <<<'EOT'
{{ $variable {{ $hello }}
EOT;
        $result = $this->compiler->compileString($template);
        $this->assertSame('{{ $variable <?php echo e($hello); ?>', $result);

        $this->assertHasErrorOnLine(1, ErrorType::UnexpectedEchoEncountered, ConstructContext::Echo);
    }

    public function testUnexpectedEchoAndEndOfContent()
    {
        $document = <<<'EOT'

{{ $variable {{ $hello
EOT;
        $result = $this->compiler->compileString($document);
        $this->assertSame($document, $result);

        $this->assertHasErrorOnLine(2, ErrorType::UnexpectedEchoEncountered, ConstructContext::Echo);
        $this->assertHasErrorOnLine(2, ErrorType::UnexpectedEndOfInput, ConstructContext::Echo);
    }

    public function testUnexpectedRawEchoAndEndOfContent()
    {
        $document = <<<'EOT'

{!! $variable {!! $hello
EOT;
        $result = $this->compiler->compileString($document);
        $this->assertSame($document, $result);

        $this->assertHasErrorOnLine(2, ErrorType::UnexpectedRawEchoEncountered, ConstructContext::RawEcho);
        $this->assertHasErrorOnLine(2, ErrorType::UnexpectedEndOfInput, ConstructContext::RawEcho);
    }

    public function testUnexpectedTripleEchoAndEndOfContent()
    {
        $document = <<<'EOT'

{{{ $variable {{{ $hello
EOT;
        $result = $this->compiler->compileString($document);
        $this->assertSame($document, $result);

        $this->assertHasErrorOnLine(2, ErrorType::UnexpectedTripleEchoEncountered, ConstructContext::TripleEcho);
        $this->assertHasErrorOnLine(2, ErrorType::UnexpectedEndOfInput, ConstructContext::TripleEcho);
    }

    public function testProducedErrorCodes()
    {
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

        $this->assertSame($expected, $errorCodes);
    }

    public function testErrorsAreCreatedFromMixedUnclosedStructures()
    {
        $template = <<<'EOT'
{{ $var1 {!! $varTwo {{{ $varThree {{ $varFour }} {{ $varFive
EOT;
        $result = $this->compiler->compileString($template);
        $expected = <<<'EXPECTED'
{{ $var1 {!! $varTwo {{{ $varThree <?php echo e($varFour); ?> {{ $varFive
EXPECTED;
        $this->assertSame($result, $expected);

        $this->assertHasErrorOnLine(1, ErrorType::UnexpectedRawEchoEncountered, ConstructContext::Echo);
        $this->assertHasErrorOnLine(1, ErrorType::UnexpectedTripleEchoEncountered, ConstructContext::RawEcho);
        $this->assertHasErrorOnLine(1, ErrorType::UnexpectedEchoEncountered, ConstructContext::TripleEcho);
        $this->assertHasErrorOnLine(1, ErrorType::UnexpectedEndOfInput, ConstructContext::Echo);
    }

    public function testUnexpectedComponentsAreDetected()
    {
        $template = <<<'EOT'

{{ $hello <x-alert message 
EOT;
        $result = $this->compiler->compileString($template);
        $this->assertSame($template, $result);

        $this->assertHasErrorOnLine(2, ErrorType::UnexpectedComponentTagEncountered, ConstructContext::Echo);
        $this->assertHasErrorOnLine(2, ErrorType::UnexpectedEndOfInput, ConstructContext::ComponentTag);
    }

    public function testUnexpectedColonComponentsAreDetected()
    {
        $template = <<<'EOT'

{{ $hello <x:alert message 
EOT;
        $result = $this->compiler->compileString($template);
        $this->assertSame($template, $result);

        $this->assertHasErrorOnLine(2, ErrorType::UnexpectedNamespacedComponentTagEncountered, ConstructContext::Echo);
        $this->assertHasErrorOnLine(2, ErrorType::UnexpectedEndOfInput, ConstructContext::ComponentTag);
    }

    public function testUnexpectedClosingComponentTagsAreDetected()
    {
        $template = <<<'EOT'

{{ $hello </x-alert 
EOT;
        $result = $this->compiler->compileString($template);
        $this->assertSame($template, $result);

        $this->assertHasErrorOnLine(2, ErrorType::UnexpectedComponentClosingTagEncountered, ConstructContext::Echo);
        $this->assertHasErrorOnLine(2, ErrorType::UnexpectedEndOfInput, ConstructContext::ComponentTag);
    }

    public function testUnexpectedColonClosingComponentTagsAreDetected()
    {
        $template = <<<'EOT'

{{ $hello </x:alert 
EOT;
        $result = $this->compiler->compileString($template);
        $this->assertSame($template, $result);

        $this->assertHasErrorOnLine(2, ErrorType::UnexpectedNamespacedComponentClosingTagEncountered, ConstructContext::Echo);
        $this->assertHasErrorOnLine(2, ErrorType::UnexpectedEndOfInput, ConstructContext::ComponentTag);
    }

    public function testUnexpectedBladeCommentsAreDetected()
    {
        $template = <<<'EOT'

    {{ $hello
        {{-- I am a comment.
EOT;
        $result = $this->compiler->compileString($template);
        $this->assertSame($template, $result);

        $this->assertHasErrorOnLine(2, ErrorType::UnexpectedCommentEncountered, ConstructContext::Echo);
        $this->assertHasErrorOnLine(3, ErrorType::UnexpectedEndOfInput, ConstructContext::Comment);
    }

    public function testUnexpectedShortPhpTagsAreDetected()
    {
        $template = <<<'EOT'

    {{ $hello
        <?= $letsGo
EOT;
        $result = $this->compiler->compileString($template);
        $this->assertSame($template, $result);

        $this->assertHasErrorOnLine(2, ErrorType::UnexpectedPhpShortOpen, ConstructContext::Echo);
        $this->assertHasErrorOnLine(3, ErrorType::UnexpectedEndOfInput, ConstructContext::PhpShortOpen);
    }

    public function testUnexpectedPhpClosingTagsAreDetected()
    {
        $template = <<<'EOT'

    {{ $hello
        ?>
EOT;
        $result = $this->compiler->compileString($template);
        $this->assertSame($template, $result);

        $this->assertHasErrorOnLine(2, ErrorType::UnexpectedPhpClosingTag, ConstructContext::Echo);
    }
}
