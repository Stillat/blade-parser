<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('chained directives', function () {
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
    expect($chained)->toHaveCount(3);
    $this->assertDirectiveContent($chained[0], 'elseif', "(\$somethingElse == 'that')");
    $this->assertDirectiveContent($chained[1], 'else');
    $this->assertDirectiveContent($chained[2], 'endif');
});
