<?php

namespace Stillat\BladeParser\Nodes;

enum EchoType
{
    /**
     * A normal echo: {{ $value }}
     */
    case Echo;
    /**
     * An echo with three curly braces: {{{ $value }}}
     */
    case TripleEcho;
    /**
     * A raw echo: {!! $value !!}
     */
    case RawEcho;
}
