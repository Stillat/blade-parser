<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('if statements are compiled', function () {
    $string = '@if (name(foo(bar)))
breeze
@endif';
    $expected = '<?php if(name(foo(bar))): ?>
breeze
<?php endif; ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});

test('switchstatements are compiled', function () {
    $string = '@switch(true)
@case(1)
foo

@case(2)
bar
@endswitch

foo

@switch(true)
@case(1)
foo

@case(2)
bar
@endswitch';
    $expected = '<?php switch(true):
case (1): ?>
foo

<?php case (2): ?>
bar
<?php endswitch; ?>

foo

<?php switch(true):
case (1): ?>
foo

<?php case (2): ?>
bar
<?php endswitch; ?>';
    expect($this->compiler->compileString($string))->toEqual($expected);
});
