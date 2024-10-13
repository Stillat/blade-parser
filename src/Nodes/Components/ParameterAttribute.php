<?php

namespace Stillat\BladeParser\Nodes\Components;

use Stillat\BladeParser\Nodes\BaseNode;

class ParameterAttribute extends BaseNode
{
    public string $content = '';

    public function clone(): ParameterAttribute
    {
        $attribute = new ParameterAttribute;
        $this->copyBasicDetailsTo($attribute);

        $attribute->content = $this->content;

        return $attribute;
    }
}
