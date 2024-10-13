<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('if statements are compiled', function () {
    $string = '@isset ($test)
breeze
@endisset';
    $expected = '<?php if(isset($test)): ?>
breeze
<?php endif; ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});
