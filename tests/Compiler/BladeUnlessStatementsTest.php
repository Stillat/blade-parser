<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('unless statements are compiled', function () {
    $string = '@unless (name(foo(bar)))
breeze
@endunless';
    $expected = '<?php if (! (name(foo(bar)))): ?>
breeze
<?php endif; ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});
