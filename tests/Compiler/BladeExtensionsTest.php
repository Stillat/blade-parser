<?php

namespace Stillat\BladeParser\Tests\Compiler;

use Stillat\BladeParser\Tests\ParserTestCase;

class BladeExtensionsTest extends ParserTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->compiler->extend(function ($value) {
            return str_replace('foo', 'bar', $value);
        });
    }

    public function testPhpDocumentsWithExtensionsAreCompiled()
    {
        $document = <<<'EOT'
<?php ?>
EOT;

        $result = $this->compiler->compileString($document);

        $this->assertSame('<?php ?>', $result);
    }

    public function testDocumentWithMultiplePhpNodesAreCompiled()
    {
        $document = <<<'EOT'
<?php $valueOne; ?>foo-one<?php $valueTwo; ?>foo-two<?php $valueThree; ?>foo-three
EOT;

        $expected = <<<'EXPECTED'
<?php $valueOne; ?>bar-one<?php $valueTwo; ?>bar-two<?php $valueThree; ?>bar-three
EXPECTED;

        $result = $this->compiler->compileString($document);

        $this->assertSame($expected, $result);
    }

    public function testDocumentWithMultiplePhpNodesContainingReplacementValue()
    {
        $document = <<<'EOT'
<?php $fooOne; ?>foo-one<?php $fooTwo; ?>foo-two<?php $fooThree; ?>foo-three
EOT;

        $expected = <<<'EXPECTED'
<?php $fooOne; ?>bar-one<?php $fooTwo; ?>bar-two<?php $fooThree; ?>bar-three
EXPECTED;

        $result = $this->compiler->compileString($document);

        $this->assertSame($expected, $result);
    }

    public function testDocumentEndingWithPhpTag()
    {
        $document = <<<'EOT'
<?php $fooOne; ?>bar-one<?php $fooTwo; ?>bar-two<?php $fooThree; ?>bar-three<?php $fooFoo; ?>
EOT;

        $expected = <<<'EXPECTED'
<?php $fooOne; ?>bar-one<?php $fooTwo; ?>bar-two<?php $fooThree; ?>bar-three<?php $fooFoo; ?>
EXPECTED;

        $result = $this->compiler->compileString($document);

        $this->assertSame($expected, $result);
    }

    public function testDocumentContainingPhpWithNewlinesAndReplacements()
    {
        $document = <<<'EOT'
<?php $fooOne; ?>foo-
    one<?php $fooTwo; 
   $foo; ?> foo-two
         <?php $fooThree; ?>foo-three
foo-foo-foo
EOT;

        $expected = <<<'EXPECTED'
<?php $fooOne; ?>bar-
    one<?php $fooTwo; 
   $foo; ?> bar-two
         <?php $fooThree; ?>bar-three
bar-bar-bar
EXPECTED;

        $result = $this->compiler->compileString($document);

        $this->assertSame($expected, $result);
    }
}
