<?php

namespace Stillat\BladeParser\Contracts;

use Stillat\BladeParser\Document\Document;

interface PathFormatter
{
    public function getPath(Document $document): string;
}
