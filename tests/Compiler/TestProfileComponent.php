<?php

namespace Stillat\BladeParser\Tests\Compiler;


use Illuminate\View\Component;

class TestProfileComponent extends Component
{
    function __construct($userId = 'foo')
    {
        $this->userId = $userId;
    }

    function render()
    {
        return 'profile';
    }
}
