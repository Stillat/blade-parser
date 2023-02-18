<?php

namespace Stillat\BladeParser\Nodes;

enum PhpTagType
{
    /**
     * Indicates a PhpTagNode was created from <?php ?> syntax.
     */
    case PhpOpenTag;
    /**
     * Indicates a PhpTagNode was created from <?= ?> syntax.
     */
    case PhpOpenTagWithEcho;
}
