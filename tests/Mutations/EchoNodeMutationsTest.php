<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Nodes\EchoNode;
use Stillat\BladeParser\Nodes\EchoType;

test('basic echo node mutations', function () {
    $doc = $this->getDocument('A{{ $echo }}B{{ $echoTwo }}C');

    /** @var EchoNode $echo */
    $echo = $doc->firstOfType(EchoNode::class);

    $echo->setInnerContent('$newVariable');
    expect($echo->isDirty())->toBeTrue();
    expect((string) $echo)->toBe('{{ $newVariable }}');

    $expected = <<<'EOT'
A{{ $newVariable }}B{{ $echoTwo }}C
EOT;

    expect((string) $doc)->toBe($expected);
});

test('changing echo type mutations', function () {
    $doc = $this->getDocument('A{{ $echo }}B{{ $echoTwo }}C');

    /** @var EchoNode $echo */
    $echo = $doc->firstOfType(EchoNode::class);

    $echo->setInnerContent('$newVariable');
    $echo->setType(EchoType::RawEcho);
    expect($echo->isDirty())->toBeTrue();
    expect($echo->type)->toBe(EchoType::RawEcho);
    expect((string) $echo)->toBe('{!! $newVariable !!}');

    $expected = <<<'EOT'
A{!! $newVariable !!}B{{ $echoTwo }}C
EOT;

    expect((string) $doc)->toBe($expected);
});

test('basic triple echo node mutations', function () {
    $doc = $this->getDocument('A{{{ $echo }}}B{{{ $echoTwo }}}C');

    /** @var EchoNode $echo */
    $echo = $doc->firstOfType(EchoNode::class);

    $echo->setInnerContent('$newVariable');
    expect($echo->isDirty())->toBeTrue();
    expect((string) $echo)->toBe('{{{ $newVariable }}}');

    $expected = <<<'EOT'
A{{{ $newVariable }}}B{{{ $echoTwo }}}C
EOT;

    expect((string) $doc)->toBe($expected);
});

test('basic raw echo node mutations', function () {
    $doc = $this->getDocument('A{!! $echo !!}B{!! $echoTwo !!}C');

    /** @var EchoNode $echo */
    $echo = $doc->firstOfType(EchoNode::class);

    $echo->setInnerContent('$newVariable');
    expect($echo->isDirty())->toBeTrue();
    expect((string) $echo)->toBe('{!! $newVariable !!}');

    $expected = <<<'EOT'
A{!! $newVariable !!}B{!! $echoTwo !!}C
EOT;

    expect((string) $doc)->toBe($expected);
});
