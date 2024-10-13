<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('if statements are compiled', function () {
    $string = '@empty ($test)
breeze
@endempty';
    $expected = '<?php if(empty($test)): ?>
breeze
<?php endif; ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});
