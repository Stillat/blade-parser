<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
test('basic comment details', function () {
    $template = <<<'EOT'
{{-- One --}}

        {{-- Two --}}
EOT;
    $doc = $this->getDocument($template);
    $comments = $doc->getComments();
    expect($comments)->toHaveCount(2);
    expect($doc->hasAnyComments())->toBeTrue();
});
