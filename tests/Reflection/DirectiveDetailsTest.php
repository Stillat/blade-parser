<?php

namespace Stillat\BladeParser\Tests\Reflection;

use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Tests\ParserTestCase;

class DirectiveDetailsTest extends ParserTestCase
{
    public function testSpannedLineCount()
    {
        $template = <<<'EOT'
@if ($something == 'something')

@elseif

@if 
    ($something
    == 
        'something')
        
@endif
EOT;
        $doc = $this->getDocument($template);
        $this->assertTrue($doc->hasAnyDirectives());
        /** @var DirectiveNode[] $ifs */
        $ifs = $doc->findDirectivesByName('if');

        $this->assertSame(1, $ifs[0]->getSpannedLineCount());
        $this->assertTrue($ifs[0]->argumentsBeginOnSameLine());
        $this->assertFalse($ifs[0]->spansMultipleLines());
        $this->assertSame(4, $ifs[1]->getSpannedLineCount());
        $this->assertTrue($ifs[1]->spansMultipleLines());
        $this->assertFalse($ifs[1]->argumentsBeginOnSameLine());
    }

    public function testInvalidSpannedLineCount()
    {
        $template = <<<'EOT'
@if ($something == 

                'something')

EOT;
        $doc = $this->getDocument($template);
        $this->assertTrue($doc->hasAnyDirectives());
        /** @var DirectiveNode[] $ifs */
        $ifs = $doc->findDirectivesByName('if');
        $ifs[0]->position = null;
        $this->assertSame(1, $ifs[0]->getSpannedLineCount());
    }

    public function testArgDistance()
    {
        $template = <<<'EOT'
@if     ($something == 'something')

@elseif

@if ($something == 'something')
        
@endif

@if($something == 'something')
        
@endif
EOT;
        $doc = $this->getDocument($template);
        /** @var DirectiveNode[] $ifs */
        $ifs = $doc->findDirectivesByName('if');

        $this->assertSame(5, $ifs[0]->getArgumentsDistance());
        $this->assertSame(1, $ifs[1]->getArgumentsDistance());
        $this->assertSame(0, $ifs[2]->getArgumentsDistance());
    }
}
