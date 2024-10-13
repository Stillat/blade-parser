<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
beforeEach(function () {
    $this->compiler->extend(function ($value) {
        return str_replace('foo', 'bar', $value);
    });
});

test('php documents with extensions are compiled', function () {
    $document = <<<'EOT'
<?php ?>
EOT;

    $result = $this->compiler->compileString($document);

    expect($result)->toBe('<?php ?>');
});

test('document with multiple php nodes are compiled', function () {
    $document = <<<'EOT'
<?php $valueOne; ?>foo-one<?php $valueTwo; ?>foo-two<?php $valueThree; ?>foo-three
EOT;

    $expected = <<<'EXPECTED'
<?php $valueOne; ?>bar-one<?php $valueTwo; ?>bar-two<?php $valueThree; ?>bar-three
EXPECTED;

    $result = $this->compiler->compileString($document);

    expect($result)->toBe($expected);
});

test('document with multiple php nodes containing replacement value', function () {
    $document = <<<'EOT'
<?php $fooOne; ?>foo-one<?php $fooTwo; ?>foo-two<?php $fooThree; ?>foo-three
EOT;

    $expected = <<<'EXPECTED'
<?php $fooOne; ?>bar-one<?php $fooTwo; ?>bar-two<?php $fooThree; ?>bar-three
EXPECTED;

    $result = $this->compiler->compileString($document);

    expect($result)->toBe($expected);
});

test('document ending with php tag', function () {
    $document = <<<'EOT'
<?php $fooOne; ?>bar-one<?php $fooTwo; ?>bar-two<?php $fooThree; ?>bar-three<?php $fooFoo; ?>
EOT;

    $expected = <<<'EXPECTED'
<?php $fooOne; ?>bar-one<?php $fooTwo; ?>bar-two<?php $fooThree; ?>bar-three<?php $fooFoo; ?>
EXPECTED;

    $result = $this->compiler->compileString($document);

    expect($result)->toBe($expected);
});

test('document containing php with newlines and replacements', function () {
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

    expect($result)->toBe($expected);
});
