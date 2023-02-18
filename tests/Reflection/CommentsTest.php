<?php

namespace Stillat\BladeParser\Tests\Reflection;

use Stillat\BladeParser\Tests\ParserTestCase;

class CommentsTest extends ParserTestCase
{
    public function testBasicCommentDetails()
    {
        $template = <<<'EOT'
{{-- One --}}

        {{-- Two --}}
EOT;
        $doc = $this->getDocument($template);
        $comments = $doc->getComments();
        $this->assertCount(2, $comments);
        $this->assertTrue($doc->hasAnyComments());
    }
}
