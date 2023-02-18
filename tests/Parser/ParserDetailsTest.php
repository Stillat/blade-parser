<?php

namespace Stillat\BladeParser\Tests\Parser;

use Stillat\BladeParser\Tests\ParserTestCase;

class ParserDetailsTest extends ParserTestCase
{
    public function testParserOriginalText()
    {
        $parser = $this->parser();
        $input = "<?php echo e(\$name); ?>\r\n\r\n";
        $parser->parse($input);
        $this->assertSame($input, $parser->getOriginalContent());
        // Newlines are internally converted.
        $this->assertNotSame($input, $parser->getParsedContent());
    }
}
