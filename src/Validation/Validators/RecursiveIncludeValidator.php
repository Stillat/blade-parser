<?php

namespace Stillat\BladeParser\Validation\Validators;

use Illuminate\Support\Str;
use Stillat\BladeParser\Compiler\CompilerServices\CoreDirectiveRetriever;
use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Validation\AbstractNodeValidator;
use Stillat\BladeParser\Validation\ValidationResult;

class RecursiveIncludeValidator extends AbstractNodeValidator
{
    public bool $requiresStructures = true;

    protected array $includes = [];

    public function __construct()
    {
        $this->includes = CoreDirectiveRetriever::instance()->getIncludeDirectiveNames();
    }

    public function validate(AbstractNode $node): ValidationResult|array|null
    {
        if (! $node instanceof DirectiveNode || ! $node->hasDocument() || ! $node->hasArguments() || ! $node->arguments->hasStringValue()) {
            return null;
        }
        if (! in_array($node->content, $this->includes)) {
            return null;
        }
        $check = Str::start($node->arguments->getStringValue().'.blade.php', '/');

        if (Str::endsWith($node->getDocument()->getFilePath(), $check) && ! $node->hasConditionParent()) {
            $innerContent = Str::limit($node->arguments->innerContent, 20);

            return $this->makeValidationResult($node, "Possible infinite recursion detected near [@{$node->content}({$innerContent})]");
        }

        return null;
    }
}
