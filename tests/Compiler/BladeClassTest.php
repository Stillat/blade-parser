<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('classes are conditionally compiled from array', function () {
    $template = <<<'EOT'
<span @class(['font-bold', 'mt-4', 'ml-2' => true, 'mr-2' => false])></span>
EOT;

    $expected = <<<'EXPECTED'
<span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['font-bold', 'mt-4', 'ml-2' => true, 'mr-2' => false]) ?>"></span>
EXPECTED;

    $result = $this->compiler->compileString($template);

    expect($result)->toBe($expected);
});
