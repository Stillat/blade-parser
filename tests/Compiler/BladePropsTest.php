<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Illuminate\View\ComponentAttributeBag;
use Stillat\BladeParser\Compiler\CompilerServices\StringUtilities;

test('props are compiled', function () {
    expect(StringUtilities::normalizeLineEndings($this->compiler->compileString('@props([\'one\' => true, \'two\' => \'string\'])')))->toBe(StringUtilities::normalizeLineEndings('<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([\'one\' => true, \'two\' => \'string\']) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([\'one\' => true, \'two\' => \'string\']); ?>
<?php foreach (array_filter(([\'one\' => true, \'two\' => \'string\']), \'is_string\', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>'));
});

test('props are extracted from parent attributes correctly', function () {
    $test1 = $test2 = $test4 = null;

    $attributes = new ComponentAttributeBag(['test1' => 'value1', 'test2' => 'value2', 'test3' => 'value3']);

    $template = $this->compiler->compileString('@props([\'test1\' => \'default\', \'test2\', \'test4\' => \'default\'])');

    ob_start();
    eval(" ?> $template <?php ");
    ob_get_clean();

    expect('value1')->toBe($test1);
    expect('value2')->toBe($test2);
    expect(isset($test3))->toBeFalse();
    expect('default')->toBe($test4);

    expect($attributes->get('test1'))->toBeNull();
    expect($attributes->get('test2'))->toBeNull();
    expect('value3')->toBe($attributes->get('test3'));
});
