<?php

namespace Stillat\BladeParser\Tests;

use PHPUnit\Framework\TestCase;
use Stillat\BladeParser\Compilers\PhpCompiler;

class ParserTestCase extends TestCase
{
    /**
     * The PhpCompiler instance.
     *
     * @var PhpCompiler
     */
    protected $compiler;

    protected function setUp(): void
    {
        $this->compiler = new PhpCompiler();

        parent::setUp();
    }
}
