<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('escaped with at directives are compiled', function () {
    expect($this->compiler->compileString('@@foreach'))->toBe('@foreach');
    expect($this->compiler->compileString('@@verbatim @@continue @@endverbatim'))->toBe('@verbatim @continue @endverbatim');
    expect($this->compiler->compileString('@@foreach($i as $x)'))->toBe('@foreach($i as $x)');
    expect($this->compiler->compileString('@@continue @@break'))->toBe('@continue @break');
    expect($this->compiler->compileString('@@foreach(
            $i as $x
        )'))->toBe('@foreach(
            $i as $x
        )');
});
