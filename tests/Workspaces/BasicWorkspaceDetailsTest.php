<?php

namespace Stillat\BladeParser\Tests\Workspaces;

use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Tests\ParserTestCase;
use Stillat\BladeParser\Workspaces\Workspace;

class BasicWorkspaceDetailsTest extends ParserTestCase
{
    protected ?Workspace $workspace = null;

    protected function setUp(): void
    {
        $this->workspace = $this->getWorkspace('one');
    }

    protected function tearDown(): void
    {
        $this->workspace->cleanUp();
    }

    public function testWorkspaceDocumentCount()
    {
        $this->assertSame(2, $this->workspace->getDocumentCount());
        $this->assertCount(2, $this->workspace->getDocuments());
        $this->assertSame(2, $this->workspace->getFileCount());
    }

    public function testWorkspaceFindDirectivesByName()
    {
        /** @var DirectiveNode[] $directives */
        $directives = $this->workspace->findDirectivesByName('include');
        $this->assertCount(2, $directives);
        $this->assertDirectiveContent($directives[0], 'include', "('something')");
        $this->assertDirectiveContent($directives[1], 'include', "('something-else')");
        $this->assertNotSame($directives[0]->getDocument(), $directives[1]->getDocument());
    }
}
