<?php

namespace Stillat\BladeParser\Tests;

class BladeIfAuthStatementsTest extends ParserTestCase
{
    public function testIfStatementsAreCompiled()
    {
        $string = '@auth("api")
breeze
@endauth';
        $expected = '<?php if(auth()->guard("api")->check()): ?>
breeze
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testPlainIfStatementsAreCompiled()
    {
        $string = '@auth
breeze
@endauth';
        $expected = '<?php if(auth()->guard()->check()): ?>
breeze
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }
}
