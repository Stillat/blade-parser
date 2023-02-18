<?php

namespace Stillat\BladeParser\Tests\Compiler;

use Stillat\BladeParser\Tests\ParserTestCase;

class BladePhpStatementsTest extends ParserTestCase
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
