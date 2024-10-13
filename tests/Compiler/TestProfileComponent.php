<?php

namespace Stillat\BladeParser\Tests\Compiler;

use Illuminate\View\Component;

class TestProfileComponent extends Component
{
    public function __construct($userId = 'foo')
    {
        $this->userId = $userId;
    }

    public function render()
    {
        return 'profile';
    }
}
