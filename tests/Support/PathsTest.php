<?php

namespace Stillat\BladeParser\Tests\Support;

use PHPUnit\Framework\TestCase;
use Stillat\BladeParser\Support\Utilities\Paths;

class PathsTest extends TestCase
{
    public function testNullPathsReturnNull()
    {
        $this->assertNull(Paths::normalizePath(null));
        $this->assertNull(Paths::normalizePathWithTrailingSlash(null));
    }

    public function testTrailingSlashIsNotDuplicated()
    {
        $this->assertSame('/path/', Paths::normalizePathWithTrailingSlash('/path/'));
    }

    public function testTrailingSlashIsAdded()
    {
        $this->assertSame('/path/', Paths::normalizePathWithTrailingSlash('/path'));
    }

    public function testBackslashesAreConverted()
    {
        $this->assertSame('/mnt/c/wsl/', Paths::normalizePathWithTrailingSlash('\\mnt\\c\\wsl'));
    }
}
