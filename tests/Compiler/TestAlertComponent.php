<?php

namespace Stillat\BladeParser\Tests\Compiler;


use Illuminate\View\Component;

class TestAlertComponent extends Component
{
    function __construct($title = 'foo', $userId = 1)
    {
        $this->title = $title;
    }

    function render()
    {
        return 'alert';
    }
}