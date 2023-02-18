<?php

namespace Stillat\BladeParser\Tests\Workspaces;

use Stillat\BladeParser\Nodes\LiteralNode;
use Stillat\BladeParser\Tests\ParserTestCase;
use Stillat\BladeParser\Workspaces\Workspace;

class WorkspaceProxyTest extends ParserTestCase
{
    protected ?Workspace $workspace = null;

    protected function setUp(): void
    {
        $this->workspace = $this->getWorkspace('two');
        $this->workspace->resolveStructures();
    }

    public function testFindDirectivesByName()
    {
        $this->assertCount(2, $this->workspace->findDirectivesByName('include'));
    }

    public function testGetComments()
    {
        $this->assertCount(4, $this->workspace->getComments());
    }

    public function testHasAnyComments()
    {
        $this->assertTrue($this->workspace->hasAnyComments());
    }

    public function testGetEchoes()
    {
        $this->assertCount(1, $this->workspace->getEchoes());
    }

    public function testGetPhpBlocks()
    {
        $this->assertCount(2, $this->workspace->getPhpBlocks());
    }

    public function testGetPhpTags()
    {
        $this->assertCount(2, $this->workspace->getPhpTags());
    }

    public function testGetVerbatimBlocks()
    {
        $this->assertCount(2, $this->workspace->getVerbatimBlocks());
    }

    public function testGetLiterals()
    {
        $this->assertCount(55, $this->workspace->getLiterals());
    }

    public function testGetDirectives()
    {
        $this->assertCount(42, $this->workspace->getDirectives());
    }

    public function testGetHasAnyDirectives()
    {
        $this->assertTrue($this->workspace->hasAnyDirectives());
    }

    public function getGetComponents()
    {
        $this->assertCount(2, $this->workspace->getComponents());
    }

    public function testGetOpeningComponentTags()
    {
        $this->assertCount(2, $this->workspace->getOpeningComponentTags());
    }

    public function testFindComponentsByTagName()
    {
        $this->assertCount(2, $this->workspace->findComponentsByTagName('profile'));
        $this->assertCount(0, $this->workspace->findComponentsByTagName('alert'));
    }

    public function testHasAnyComponents()
    {
        $this->assertTrue($this->workspace->hasAnyComponents());
    }

    public function testHasDirective()
    {
        $this->assertTrue($this->workspace->hasDirective('include'));
    }

    public function testAllOfType()
    {
        $this->assertCount(55, $this->workspace->allOfType(LiteralNode::class));
    }

    public function testAllNotOfType()
    {
        $this->assertCount(55, $this->workspace->allNotOfType(LiteralNode::class));
    }

    public function testGetAllStructures()
    {
        $this->assertCount(18, $this->workspace->getAllStructures());
    }

    public function testGetRootStructures()
    {
        $this->assertCount(6, $this->workspace->getRootStructures());
    }

    public function testGetAllSwitchStatements()
    {
        $this->assertCount(4, $this->workspace->getAllSwitchStatements());
    }

    public function testGetRootSwitchStatements()
    {
        $this->assertCount(2, $this->workspace->getRootSwitchStatements());
    }

    public function testGetAllConditions()
    {
        $this->assertCount(4, $this->workspace->getAllConditions());
    }

    public function testGetRootConditions()
    {
        $this->assertCount(2, $this->workspace->getRootConditions());
    }

    public function testGetAllForElse()
    {
        $this->assertCount(4, $this->workspace->getAllForElse());
    }

    public function testGetRootForElse()
    {
        $this->assertCount(2, $this->workspace->getRootForElse());
    }
}
