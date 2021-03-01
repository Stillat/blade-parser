<?php

namespace Stillat\BladeParser\Printers\Php\Concerns;

use Stillat\BladeParser\Nodes\Node;

trait PrintsTranslations
{

    protected function print_lang(Node $node)
    {
        if ($node->hasInnerExpression()) {
            if ($this->isString($node->innerContent()) || $this->isFunctionCall($node->innerContent())) {
                return '<?php echo app(\'translator\')->get(' . $node->innerContent() . '); ?>';
            } else {
                return '<?php \$__env->startTranslation(' . $node->innerContent() . '); ?>';
            }
        }

        return '<?php $__env->startTranslation(); ?>';
    }

    protected function print_choice(Node $node)
    {
        return "<?php echo app('translator')->choice({$node->innerContent()}); ?>";
    }

    protected function print_endlang(Node $node)
    {
        return '<?php echo $__env->renderTranslation(); ?>';
    }

}