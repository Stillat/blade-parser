<?php

namespace Stillat\BladeParser\Tests\Compiler;

use Stillat\BladeParser\Tests\ParserTestCase;

class BladeEchoTest extends ParserTestCase
{
    public function testEchosAreCompiled()
    {
        $this->assertSame('<?php echo $name; ?>', $this->compiler->compileString('{!!$name!!}'));
        $this->assertSame('<?php echo $name; ?>', $this->compiler->compileString('{!! $name !!}'));
        $this->assertSame('<?php echo $name; ?>', $this->compiler->compileString('{!!
            $name
        !!}'));

        $this->assertSame('<?php echo e($name); ?>', $this->compiler->compileString('{{{$name}}}'));
        $this->assertSame('<?php echo e($name); ?>', $this->compiler->compileString('{{$name}}'));
        $this->assertSame('<?php echo e($name); ?>', $this->compiler->compileString('{{ $name }}'));
        $this->assertSame('<?php echo e($name); ?>', $this->compiler->compileString('{{
            $name
        }}'));
        $this->assertSame("<?php echo e(\$name); ?>\n\n", $this->compiler->compileString("{{ \$name }}\n\n"));
        $this->assertSame("<?php echo e(\$name); ?>\r\n\r\n", $this->compiler->compileString("{{ \$name }}\r\n\r\n"));
        $this->assertSame("<?php echo e(\$name); ?>\n\n", $this->compiler->compileString("{{ \$name }}\n\n"));
        $this->assertSame("<?php echo e(\$name); ?>\r\n\r\n", $this->compiler->compileString("{{ \$name }}\r\n\r\n"));

        $this->assertSame('<?php echo e("Hello world or foo"); ?>',
            $this->compiler->compileString('{{ "Hello world or foo" }}'));
        $this->assertSame('<?php echo e("Hello world or foo"); ?>',
            $this->compiler->compileString('{{"Hello world or foo"}}'));
        $this->assertSame('<?php echo e($foo + $or + $baz); ?>', $this->compiler->compileString('{{$foo + $or + $baz}}'));
        $this->assertSame('<?php echo e("Hello world or foo"); ?>', $this->compiler->compileString('{{
            "Hello world or foo"
        }}'));

        $this->assertSame('<?php echo e(\'Hello world or foo\'); ?>',
            $this->compiler->compileString('{{ \'Hello world or foo\' }}'));
        $this->assertSame('<?php echo e(\'Hello world or foo\'); ?>',
            $this->compiler->compileString('{{\'Hello world or foo\'}}'));
        $this->assertSame('<?php echo e(\'Hello world or foo\'); ?>', $this->compiler->compileString('{{
            \'Hello world or foo\'
        }}'));

        $this->assertSame('<?php echo e(myfunc(\'foo or bar\')); ?>',
            $this->compiler->compileString('{{ myfunc(\'foo or bar\') }}'));
        $this->assertSame('<?php echo e(myfunc("foo or bar")); ?>',
            $this->compiler->compileString('{{ myfunc("foo or bar") }}'));
        $this->assertSame('<?php echo e(myfunc("$name or \'foo\'")); ?>',
            $this->compiler->compileString('{{ myfunc("$name or \'foo\'") }}'));
    }

    public function testEscapedWithAtEchosAreCompiled()
    {
        $this->assertSame('{{$name}}', $this->compiler->compileString('@{{$name}}'));
        $this->assertSame('{{ $name }}', $this->compiler->compileString('@{{ $name }}'));
        $this->assertSame('{{
            $name
        }}',
            $this->compiler->compileString('@{{
            $name
        }}'));
        $this->assertSame('{{ $name }}
            ',
            $this->compiler->compileString('@{{ $name }}
            '));
    }

    public function testEchoWithDoubleEncoding()
    {
        $template = <<<'EOT'
{{ $name }}
EOT;

        $this->compiler->withDoubleEncoding();
        $result = $this->compiler->compileString($template);

        $expected = <<<'EXPECTED'
<?php echo e($name, true); ?>
EXPECTED;

        $this->assertSame($expected, $result);
    }

    public function testEchoWithoutDoubleEncoding()
    {
        $template = <<<'EOT'
{{ $name }}
EOT;

        $this->compiler->withoutDoubleEncoding();
        $result = $this->compiler->compileString($template);

        $expected = <<<'EXPECTED'
<?php echo e($name, false); ?>
EXPECTED;

        $this->assertSame($expected, $result);
    }
}
