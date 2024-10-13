<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Nodes\PhpBlockNode;

test('php block does not consume literal character', function () {
    $template = <<<'EOT'
start @php
 
 
@endphp end
EOT;
    $nodes = $this->parseNodes($template);
    expect($nodes)->toHaveCount(3);

    $this->assertLiteralContent($nodes[0], 'start ');
    $this->assertLiteralContent($nodes[2], ' end');

    expect($nodes[1])->toBeInstanceOf(PhpBlockNode::class);
});

test('many opening php block directives', function () {
    $template = <<<'EOT'
@php @php @php $counter++;
@endphp
EOT;
    $expected = <<<'EXP'
<?php @php @php $counter++; ?>
EXP;

    expect($this->compiler->compileString($template))->toBe($expected);
});

test('neighboring php block directives', function () {
    $template = <<<'EOT'
@php
    $counter += 1;
@endphp @php
    $counter += 2;
@endphp
EOT;
    $expected = <<<'EXP'
<?php $counter += 1; ?> <?php $counter += 2; ?>
EXP;

    expect($this->compiler->compileString($template))->toBe($expected);
});

test('detached php block directives with valid php blocks', function () {
    $template = <<<'EOT'
@php @php
$counter += 1;
@endphp @php
$counter += 2;
@endphp @php @php @php @php $counter += 3; @endphp
EOT;
    $expected = <<<'EXP'
<?php @php
$counter += 1; ?> <?php $counter += 2; ?> <?php @php @php @php $counter += 3; ?>
EXP;

    expect($this->compiler->compileString($template))->toBe($expected);
});

test('php blocks containing loops', function () {
    $template = <<<'EOT'
@php $counter++;
for($i = 0; $i++;$=) {}
@endphp @php $counter_two++;
for($i = 0; $i++;$=two) {}
@endphp
EOT;
    $expected = <<<'EXP'
<?php $counter++;
for($i = 0; $i++;$=) {} ?> <?php $counter_two++;
for($i = 0; $i++;$=two) {} ?>
EXP;
    expect($this->compiler->compileString($template))->toBe($expected);
});
