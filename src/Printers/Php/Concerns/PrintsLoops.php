<?php

namespace Stillat\BladeParser\Printers\Php\Concerns;

use Stillat\BladeParser\Nodes\Node;

trait PrintsLoops
{
    protected $forElseCounter = 0;

    protected function print_break(Node $node)
    {
        if ($node->hasInnerExpression()) {
            if ($node->innerContentIsIntegerEquivalent()) {
                if (intval($node->innerContent()) < 0) {
                    return '<?php break 1; ?>';
                } else {
                    return '<?php break '.trim($node->innerContent()).'; ?>';
                }
            } else {
                return '<?php if('.$node->innerContent().') break; ?>';
            }
        }

        return '<?php break; ?>';
    }

    protected function print_for(Node $node)
    {
        return '<?php for('.$node->innerContent().'): ?>';
    }

    protected function print_endfor(Node $node)
    {
        return '<?php endfor; ?>';
    }

    protected function print_continue(Node $node)
    {
        if ($node->hasInnerExpression()) {
            if ($node->innerContentIsIntegerEquivalent()) {
                if (intval($node->innerContent()) < 0) {
                    return '<?php continue 1; ?>';
                } else {
                    return '<?php continue '.trim($node->innerContent()).'; ?>';
                }
            } else {
                return '<?php if('.$node->innerContent().') continue; ?>';
            }
        }

        return '<?php continue; ?>';
    }

    protected function print_foreach(Node $node)
    {
        preg_match('/ *(.*) +as *(.*)$/is', $node->innerContent(), $matches);

        $iteratee = trim($matches[1]);

        $iteration = trim($matches[2]);

        $initLoop = "\$__currentLoopData = {$iteratee}; \$__env->addLoop(\$__currentLoopData);";

        $iterateLoop = '$__env->incrementLoopIndices(); $loop = $__env->getLastLoop();';

        return "<?php {$initLoop} foreach(\$__currentLoopData as {$iteration}): {$iterateLoop} ?>";
    }

    protected function print_endforeach(Node $node)
    {
        return '<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>';
    }

    protected function print_forelse(Node $node)
    {
        $empty = '$__empty_'.++$this->forElseCounter;

        preg_match('/ *(.*) +as *(.*)$/is', $node->innerContent(), $matches);

        $iteratee = trim($matches[1]);

        $iteration = trim($matches[2]);

        $initLoop = "\$__currentLoopData = {$iteratee}; \$__env->addLoop(\$__currentLoopData);";

        $iterateLoop = '$__env->incrementLoopIndices(); $loop = $__env->getLastLoop();';

        return "<?php {$empty} = true; {$initLoop} foreach(\$__currentLoopData as {$iteration}): {$iterateLoop} {$empty} = false; ?>";
    }

    protected function print_empty(Node $node)
    {
        if ($node->hasInnerExpression()) {
            return '<?php if(empty('.$node->innerContent().')): ?>';
        }

        $empty = '$__empty_'.$this->forElseCounter--;

        return "<?php endforeach; \$__env->popLoop(); \$loop = \$__env->getLastLoop(); if ({$empty}): ?>";
    }

    protected function print_endempty(Node $node)
    {
        return $this->phpEndIf();
    }

    protected function print_endforelse(Node $node)
    {
        return $this->phpEndIf();
    }

    protected function print_while(Node $node)
    {
        return '<?php while('.$node->innerContent().'): ?>';
    }

    protected function print_endwhile(Node $node)
    {
        return '<?php endwhile; ?>';
    }
}
