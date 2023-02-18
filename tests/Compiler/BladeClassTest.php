<?php

namespace Stillat\BladeParser\Tests\Compiler;

use Stillat\BladeParser\Tests\ParserTestCase;

class BladeClassTest extends ParserTestCase
{
    public function testClassesAreConditionallyCompiledFromArray()
    {
        $template = <<<'EOT'
<span @class(['font-bold', 'mt-4', 'ml-2' => true, 'mr-2' => false])></span>
EOT;

        $expected = <<<'EXPECTED'
<span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['font-bold', 'mt-4', 'ml-2' => true, 'mr-2' => false]) ?>"></span>
EXPECTED;

        $result = $this->compiler->compileString($template);

        $this->assertSame($expected, $result);
    }
}
