<?php

namespace Stillat\BladeParser\Tests\Reflection;

use Stillat\BladeParser\Tests\ParserTestCase;

class ChainedClosingDirectivesTest extends ParserTestCase
{
    public function testChainedDirectives()
    {
        $template = <<<'EOT'
One
@if ($that == 'this')
    Two
@elseif ($somethingElse == 'that')
    Three
@else
    Four
@endif 
Five
EOT;
        $doc = $this->getDocument($template);
        $doc->resolveStructures();

        $ifStatement = $doc->getDirectives()->first();
        $chained = $ifStatement->getChainedClosingDirectives();
        $this->assertCount(3, $chained);
        $this->assertDirectiveContent($chained[0], 'elseif', "(\$somethingElse == 'that')");
        $this->assertDirectiveContent($chained[1], 'else');
        $this->assertDirectiveContent($chained[2], 'endif');
    }
}
