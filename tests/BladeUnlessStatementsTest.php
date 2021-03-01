<?php

namespace Stillat\BladeParser\Tests;

class BladeUnlessStatementsTest extends ParserTestCase
{
    public function testUnlessStatementsAreCompiled()
    {
        $string = '@unless (name(foo(bar)))
breeze
@endunless';
        $expected = '<?php if (! (name(foo(bar)))): ?>
breeze
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }
}