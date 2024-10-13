<?php

namespace Stillat\BladeParser\Tests\Compiler;

use Illuminate\View\Component;

class TestAlertComponent extends Component
{
    public function __construct($title = 'foo', $userId = 1)
    {
        $this->title = $title;
    }

    public function render()
    {
        return 'alert';
    }
}
