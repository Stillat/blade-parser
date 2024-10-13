<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Compiler\CompilerServices\ArgStringSplitter;

beforeEach(function () {
    $this->splitter = new ArgStringSplitter();
});

test('argument string splitting', function () {
    $input = <<<'EOT'
["one, two", $var1, $var2], $hello, 12345.23, bar, baz, (1,2,3,4,), "foo, bar, baz"
EOT;

    expect($this->splitter->split($input))->toBe([
        '["one, two", $var1, $var2]',
        '$hello',
        '12345.23',
        'bar',
        'baz',
        '(1,2,3,4,)',
        '"foo, bar, baz"',
    ]);

    $input = <<<'EOT'
[["one, two", $var1, $var2], $hello, 12345.23, bar, baz, (1,2,3,4,), "foo, bar, baz"]
EOT;

    expect($this->splitter->split($input))->toBe([
        $input,
    ]);

    $input = <<<'EOT'
(["one, two", $var1, $var2], $hello, 12345.23, bar, baz, (1,2,3,4,), "foo, bar, baz")
EOT;

    expect($this->splitter->split($input))->toBe([
        $input,
    ]);

    $input = <<<'EOT'
[["one, two", $var1, $var2], $hello, 12345.23], bar, baz, (1,2,3,4,), "foo, bar, baz"
EOT;

    expect($this->splitter->split($input))->toBe([
        '[["one, two", $var1, $var2], $hello, 12345.23]',
        'bar',
        'baz',
        '(1,2,3,4,)',
        '"foo, bar, baz"',
    ]);

    $input = <<<'EOT'
[["one, two", $var1, $var2], $hello, 12345.23], [bar, baz, (1,2,3,4,), "foo, bar, baz"]
EOT;

    expect($this->splitter->split($input))->toBe([
        '[["one, two", $var1, $var2], $hello, 12345.23]',
        '[bar, baz, (1,2,3,4,), "foo, bar, baz"]',
    ]);

    $input = <<<'EOT'
[[[[[["one, two", $var1, $var2], $hello, 12345.23]]]]], [bar, baz, (1,2,3,4,), "foo, bar, baz"]
EOT;

    expect($this->splitter->split($input))->toBe([
        '[[[[[["one, two", $var1, $var2], $hello, 12345.23]]]]]',
        '[bar, baz, (1,2,3,4,), "foo, bar, baz"]',
    ]);

    $input = <<<'EOT'
[[[[[["one, two", $var1, $var2], $hello, 12345.23]]]]], [bar, baz, (1,2,3,4,), "foo, bar, baz"], (true == false) ? $this : $that
EOT;

    expect($this->splitter->split($input))->toBe([
        '[[[[[["one, two", $var1, $var2], $hello, 12345.23]]]]]',
        '[bar, baz, (1,2,3,4,), "foo, bar, baz"]',
        '(true == false) ? $this : $that',
    ]);
});