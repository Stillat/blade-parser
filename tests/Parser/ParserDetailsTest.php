<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('parser original text', function () {
    $parser = $this->parser();
    $input = "<?php echo e(\$name); ?>\r\n\r\n";
    $parser->parse($input);
    expect($parser->getOriginalContent())->toBe($input);

    // Newlines are internally converted.
    $this->assertNotSame($input, $parser->getParsedContent());
});
