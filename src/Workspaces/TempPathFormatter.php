<?php

namespace Stillat\BladeParser\Workspaces;

use Stillat\BladeParser\Contracts\PathFormatter;
use Stillat\BladeParser\Document\Document;

class TempPathFormatter implements PathFormatter
{
    public function getPath(Document $document): string
    {
        return 'temp_'.sha1($document->getFilePath()).'.php';
    }
}
