<?php

namespace Stillat\BladeParser\Parser;

use Stillat\BladeParser\Errors\ErrorType;

class ScanResult
{
    public ?int $abandonedOffset = null;

    public bool $didAbandon = false;

    public ErrorType $abandonReason = ErrorType::Unknown;

    public string $content = '';

    public int $offset = -1;
}
