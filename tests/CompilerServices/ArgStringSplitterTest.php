<?php

namespace Stillat\BladeParser\Tests\CompilerServices;

use Stillat\BladeParser\Compiler\CompilerServices\ArgStringSplitter;
use Stillat\BladeParser\Tests\ParserTestCase;

class ArgStringSplitterTest extends ParserTestCase
{
    protected ArgStringSplitter $splitter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->splitter = new ArgStringSplitter();
    }

    public function testArgumentStringSplitting()
    {
        $input = <<<'EOT'
["one, two", $var1, $var2], $hello, 12345.23, bar, baz, (1,2,3,4,), "foo, bar, baz"
EOT;

        $this->assertSame([
            '["one, two", $var1, $var2]',
            '$hello',
            '12345.23',
            'bar',
            'baz',
            '(1,2,3,4,)',
            '"foo, bar, baz"',
        ], $this->splitter->split($input));

        $input = <<<'EOT'
[["one, two", $var1, $var2], $hello, 12345.23, bar, baz, (1,2,3,4,), "foo, bar, baz"]
EOT;

        $this->assertSame([
            $input,
        ], $this->splitter->split($input));

        $input = <<<'EOT'
(["one, two", $var1, $var2], $hello, 12345.23, bar, baz, (1,2,3,4,), "foo, bar, baz")
EOT;

        $this->assertSame([
            $input,
        ], $this->splitter->split($input));

        $input = <<<'EOT'
[["one, two", $var1, $var2], $hello, 12345.23], bar, baz, (1,2,3,4,), "foo, bar, baz"
EOT;

        $this->assertSame([
            '[["one, two", $var1, $var2], $hello, 12345.23]',
            'bar',
            'baz',
            '(1,2,3,4,)',
            '"foo, bar, baz"',
        ], $this->splitter->split($input));

        $input = <<<'EOT'
[["one, two", $var1, $var2], $hello, 12345.23], [bar, baz, (1,2,3,4,), "foo, bar, baz"]
EOT;

        $this->assertSame([
            '[["one, two", $var1, $var2], $hello, 12345.23]',
            '[bar, baz, (1,2,3,4,), "foo, bar, baz"]',
        ], $this->splitter->split($input));

        $input = <<<'EOT'
[[[[[["one, two", $var1, $var2], $hello, 12345.23]]]]], [bar, baz, (1,2,3,4,), "foo, bar, baz"]
EOT;

        $this->assertSame([
            '[[[[[["one, two", $var1, $var2], $hello, 12345.23]]]]]',
            '[bar, baz, (1,2,3,4,), "foo, bar, baz"]',
        ], $this->splitter->split($input));

        $input = <<<'EOT'
[[[[[["one, two", $var1, $var2], $hello, 12345.23]]]]], [bar, baz, (1,2,3,4,), "foo, bar, baz"], (true == false) ? $this : $that
EOT;

        $this->assertSame([
            '[[[[[["one, two", $var1, $var2], $hello, 12345.23]]]]]',
            '[bar, baz, (1,2,3,4,), "foo, bar, baz"]',
            '(true == false) ? $this : $that',
        ], $this->splitter->split($input));
    }
}
