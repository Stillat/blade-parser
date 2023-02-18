<?php

namespace Stillat\BladeParser\Tests\Compiler;

use Stillat\BladeParser\Tests\ParserTestCase;

class BladeErrorTest extends ParserTestCase
{
    public function testPhpStatementsWithExpressionAreCompiled()
    {
        $string = '@php($set = true)';
        $expected = '<?php ($set = true); ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testStringWithParenthesisWithEndPHP()
    {
        $string = "@php(\$data = ['related_to' => 'issue#45388'];) {{ \$data }} @endphp";
        $laravelExpected = "<?php(\$data = ['related_to' => 'issue#45388'];) {{ \$data }} ?>";

        // Note: The expected output has been changed to ensure that the
        // @endphp does not appear in the output. At the moment
        // @php followed by arguments does not begin a blocked region.
        // The PHP contents are output, as well as the intended echo.
        // The @endphp compiler simply outputs an empty string.

        $expected = <<<'EXPECTED'
<?php ($data = ['related_to' => 'issue#45388'];); ?> <?php echo e($data); ?> 
EXPECTED;

        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testPhpStatementsWithoutExpressionAreIgnored()
    {
        $string = '@php';
        $expected = '@php';
        $this->assertEquals($expected, $this->compiler->compileString($string));

        $string = '{{ "Ignore: @php" }}';
        $expected = '<?php echo e("Ignore: @php"); ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testPhpStatementsDontParseBladeCode()
    {
        $string = '@php echo "{{ This is a blade tag }}" @endphp';
        $expected = '<?php echo "{{ This is a blade tag }}" ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testVerbatimAndPhpStatementsDontGetMixedUp()
    {
        $string = "@verbatim {{ Hello, I'm not blade! }}"
            ."\n@php echo 'And I'm not PHP!' @endphp"
            ."\n@endverbatim {{ 'I am Blade' }}"
            ."\n@php echo 'I am PHP {{ not Blade }}' @endphp";

        $expected = <<<'EXPECTED'
 {{ Hello, I'm not blade! }}
@php echo 'And I'm not PHP!' @endphp
 <?php echo e('I am Blade'); ?>
<?php echo 'I am PHP {{ not Blade }}' ?>
EXPECTED;

        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testStringWithParenthesisCannotBeCompiled()
    {
        $string = "@php(\$data = ['test' => ')'])";

        $expected = "<?php (\$data = ['test' => ')']); ?>";

        // Note: In the original Laravel test for this
        // is what is asserted to be the same. Handling
        // strings with parenthesis is supported by
        // this parser, so the test has been updated
        // to reflect that difference in parsing.
        $actual = "<?php (\$data = ['test' => '); ?>'])";

        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testStringWithEmptyStringDataValue()
    {
        $string = "@php(\$data = ['test' => ''])";

        $expected = "<?php (\$data = ['test' => '']); ?>";

        $this->assertEquals($expected, $this->compiler->compileString($string));

        $string = "@php(\$data = ['test' => \"\"])";

        $expected = "<?php (\$data = ['test' => \"\"]); ?>";

        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testStringWithEscapingDataValue()
    {
        $string = "@php(\$data = ['test' => 'won\\'t break'])";

        $expected = "<?php (\$data = ['test' => 'won\\'t break']); ?>";

        $this->assertEquals($expected, $this->compiler->compileString($string));

        $string = "@php(\$data = ['test' => \"\\\"escaped\\\"\"])";

        $expected = "<?php (\$data = ['test' => \"\\\"escaped\\\"\"]); ?>";

        $this->assertEquals($expected, $this->compiler->compileString($string));
    }
}
