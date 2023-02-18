<?php

namespace Stillat\BladeParser\Parser;

class IndexElement
{
    public IndexElementType $type = IndexElementType::Directive;

    public int $startOffset = 0;

    public string $content = '';
}
