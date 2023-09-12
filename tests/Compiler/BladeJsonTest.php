<?php

namespace Stillat\BladeParser\Tests\Compiler;

use Stillat\BladeParser\Tests\ParserTestCase;

class BladeJsonTest extends ParserTestCase
{
    public function testStatementIsCompiledWithSafeDefaultEncodingOptions()
    {
        $string = 'var foo = @json($var);';
        $expected = 'var foo = <?php echo json_encode($var, 15, 512) ?>;';

        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testEncodingOptionsCanBeOverwritten()
    {
        $string = 'var foo = @json($var, JSON_HEX_TAG);';
        $expected = 'var foo = <?php echo json_encode($var, JSON_HEX_TAG, 512) ?>;';

        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testComplexJsonExpressionsCanBeCompiled()
    {
        $string = 'var foo = @json(DB::query()->selectRaw("1, CONCAT(2, \' \', 3) AS name")->get())';
        $expected = <<<'EXPECTED'
var foo = <?php echo json_encode(DB::query()->selectRaw("1, CONCAT(2, ' ', 3) AS name")->get(), 15, 512) ?>
EXPECTED;

        $this->assertEquals($expected, $this->compiler->compileString($string));
    }
}
