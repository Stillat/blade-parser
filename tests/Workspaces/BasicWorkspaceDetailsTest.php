<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Workspaces\Workspace;


beforeEach(function () {
    $this->workspace = $this->getWorkspace('one');
});

afterEach(function () {
    $this->workspace->cleanUp();
});

test('workspace document count', function () {
    expect($this->workspace->getDocumentCount())->toBe(2);
    expect($this->workspace->getDocuments())->toHaveCount(2);
    expect($this->workspace->getFileCount())->toBe(2);
});

test('workspace find directives by name', function () {
    /** @var DirectiveNode[] $directives */
    $directives = $this->workspace->findDirectivesByName('include');
    expect($directives)->toHaveCount(2);
    $this->assertDirectiveContent($directives[0], 'include', "('something')");
    $this->assertDirectiveContent($directives[1], 'include', "('something-else')");
    $this->assertNotSame($directives[0]->getDocument(), $directives[1]->getDocument());
});