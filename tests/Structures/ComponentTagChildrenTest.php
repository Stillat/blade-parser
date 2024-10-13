<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\LiteralNode;

test('basic component tag children', function () {
    $template = <<<'EOT'
One <x-alert> One @include('test') Two </x-alert> Three
EOT;
    $doc = $this->getDocument($template);
    $doc->resolveStructures();

    /** @var ComponentNode $alert */
    $alert = $doc->findComponentsByTagName('alert')->first();

    expect($alert)->not->toBeNull();
    expect($alert->childNodes)->toHaveCount(3);
    expect($alert->childNodes[0])->toBeInstanceOf(LiteralNode::class);
    expect($alert->childNodes[0]->content)->toBe(' One ');
    expect($alert->childNodes[1])->toBeInstanceOf(DirectiveNode::class);
    $this->assertDirectiveContent($alert->childNodes[1], 'include', "('test')");
    expect($alert->childNodes[2])->toBeInstanceOf(LiteralNode::class);
    expect($alert->childNodes[2]->content)->toBe(' Two ');
});
