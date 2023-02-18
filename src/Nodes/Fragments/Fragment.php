<?php

namespace Stillat\BladeParser\Nodes\Fragments;

use Stillat\BladeParser\Nodes\BaseNode;
use Stillat\BladeParser\Nodes\Position;

class Fragment extends BaseNode
{
    public function __construct()
    {
        parent::__construct();

        $this->position = new Position();
    }
}
