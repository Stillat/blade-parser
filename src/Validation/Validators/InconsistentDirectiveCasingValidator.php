<?php

namespace Stillat\BladeParser\Validation\Validators;

use Stillat\BladeParser\Compiler\CompilerServices\CoreDirectiveRetriever;
use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Validation\AbstractNodeValidator;
use Stillat\BladeParser\Validation\ValidationResult;
use Stillat\BladeParser\Validation\Validators\Concerns\AcceptsCustomDirectives;
use Stillat\BladeParser\Validation\Validators\Concerns\CanIgnoreDirectives;

class InconsistentDirectiveCasingValidator extends AbstractNodeValidator
{
    use CanIgnoreDirectives, AcceptsCustomDirectives;

    protected array $coreDirectives = [];

    protected ?array $cachedDirectiveMapping = null;

    protected function populateDirectiveCache()
    {
        if ($this->cachedDirectiveMapping == null) {
            foreach ($this->coreDirectives as $directive) {
                $this->cachedDirectiveMapping[mb_strtolower($directive)] = $directive;
            }

            foreach ($this->customDirectives as $directive) {
                $this->cachedDirectiveMapping[mb_strtolower($directive)] = $directive;
            }
        }
    }

    public function __construct()
    {
        $this->coreDirectives = CoreDirectiveRetriever::instance()->getDirectiveNames();
    }

    public function validate(AbstractNode $node): ?ValidationResult
    {
        if (! $node instanceof DirectiveNode || $this->shouldIgnore($node)) {
            return null;
        }

        $this->populateDirectiveCache();
        $checkName = mb_strtolower($node->content);

        if (array_key_exists($checkName, $this->cachedDirectiveMapping) && $node->content != $this->cachedDirectiveMapping[$checkName]) {
            return $this->makeValidationResult($node, "Inconsistent casing for [@{$node->content}]; expecting [@{$this->cachedDirectiveMapping[$checkName]}]");
        }

        return null;
    }
}
