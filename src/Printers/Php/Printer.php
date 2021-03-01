<?php

namespace Stillat\BladeParser\Printers\Php;

use Stillat\BladeParser\Analyzers\RuntimeTypes;
use Stillat\BladeParser\Nodes\Node;
use Stillat\BladeParser\Parsers\Concerns\ManagesNewLines;
use Stillat\BladeParser\Printers\AbstractNodePrinter;
use Stillat\BladeParser\Printers\Php\Concerns\PrintsAppend;
use Stillat\BladeParser\Printers\Php\Concerns\PrintsAuthorizations;
use Stillat\BladeParser\Printers\Php\Concerns\PrintsConditionals;
use Stillat\BladeParser\Printers\Php\Concerns\PrintsEchos;
use Stillat\BladeParser\Printers\Php\Concerns\PrintsEnvironmentConditionals;
use Stillat\BladeParser\Printers\Php\Concerns\PrintsErrors;
use Stillat\BladeParser\Printers\Php\Concerns\PrintsExtends;
use Stillat\BladeParser\Printers\Php\Concerns\PrintsHelpers;
use Stillat\BladeParser\Printers\Php\Concerns\PrintsIncludes;
use Stillat\BladeParser\Printers\Php\Concerns\PrintsInjections;
use Stillat\BladeParser\Printers\Php\Concerns\PrintsJson;
use Stillat\BladeParser\Printers\Php\Concerns\PrintsLiterals;
use Stillat\BladeParser\Printers\Php\Concerns\PrintsLoops;
use Stillat\BladeParser\Printers\Php\Concerns\PrintsPhp;
use Stillat\BladeParser\Printers\Php\Concerns\PrintsSectionsAndLayout;
use Stillat\BladeParser\Printers\Php\Concerns\PrintsStacks;
use Stillat\BladeParser\Printers\Php\Concerns\PrintsTranslations;
use Stillat\BladeParser\Printers\Php\Concerns\PrintsVerbatim;

class Printer extends AbstractNodePrinter
{
    use PrintsAuthorizations, PrintsConditionals, PrintsAppend,
        PrintsLiterals, PrintsLoops, PrintsEchos, PrintsErrors,
        PrintsTranslations, PrintsExtends, PrintsHelpers, PrintsIncludes,
        PrintsInjections, PrintsJson, PrintsVerbatim, PrintsPhp,
        PrintsStacks,
        ManagesNewLines, PrintsSectionsAndLayout, PrintsEnvironmentConditionals;

    /**
     * @var string
     */
    protected $buffer = '';
    protected $customDirectiveHandlers = [];
    /**
     * @var RuntimeTypes
     */
    private $runtimeTypeAnalyzer = null;

    public function __construct()
    {
        $this->runtimeTypeAnalyzer = new RuntimeTypes();
    }

    public function setCustomDirectiveHandlers($customDirectives)
    {
        foreach ($customDirectives as $name => $handler) {
            $this->customDirectiveHandlers['print_' . mb_strtolower($name)] = $handler;
        }
    }

    public function printNode(Node $node)
    {
        $targetMethod = 'print_' . mb_strtolower($node->getSubType());

        // Check the custom handlers first. This will allow the
        // custom directives to overwrite the core directives.
        if (array_key_exists($targetMethod, $this->customDirectiveHandlers)) {
            // Pass the node's inner content for compatibility with existing directives.
            // But we will also pass the current Node as an optional second parameter.
            $this->buffer .= $this->customDirectiveHandlers[$targetMethod]($node->innerContent(), $node);
        } else {
            if (method_exists($this, $targetMethod)) {
                $this->buffer .= call_user_func([$this, $targetMethod], $node);
            } else {
                ray('Missing Printer: ' . $targetMethod);
            }
        }
    }

    public function getContents()
    {
        return $this->adjustNewLines($this->buffer);
    }

    public function clearContents()
    {
        $this->buffer = '';
    }

    protected function print_comment(Node $node)
    {
        return '';
    }

    private function phpEndIf()
    {
        return '<?php endif; ?>';
    }

    private function getType($input)
    {
        return $this->runtimeTypeAnalyzer->inferType($input);
    }

    private function isFunctionCall($input)
    {
        return $this->runtimeTypeAnalyzer->testType($input, RuntimeTypes::TYPE_FUNCTION_CALL);
    }

    private function isString($input)
    {
        return $this->runtimeTypeAnalyzer->testType($input, RuntimeTypes::TYPE_STRING);
    }
}
