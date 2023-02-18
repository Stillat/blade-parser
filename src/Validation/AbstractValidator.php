<?php

namespace Stillat\BladeParser\Validation;

use Illuminate\Support\Str;
use Stillat\BladeParser\Compiler\CompilerServices\StringUtilities;
use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\DirectiveNode;

abstract class AbstractValidator
{
    public bool $requiresStructures = false;

    /**
     * A list of method names to ignore when automatically loaded configuration.
     *
     * @var array|string[]
     */
    private static array $protectedMethods = [
        'validate', 'getDirectiveArgContents', 'loadConfiguration',
        'containsCustomDirective', 'shouldIgnore',
    ];

    /**
     * Attempts to load the provided configuration items into the validator instance.
     *
     * Configuration items follow a naming convention. For example, if we
     * had the configuration entry "start_line_number", there should be
     * a corresponding "setStartLineNumber" method on the validator.
     *
     * The `loadConfiguration()` method is automatically invoked on
     * all core validator instances. However, it is *not* called
     * automatically on third-party validator instances to
     * provide the developer the opportunity to configure
     * their validation systems however they like.
     *
     * @param  array  $config The configuration.
     */
    public function loadConfiguration(array $config): void
    {
        foreach ($config as $option => $value) {
            $setMethod = Str::camel('set_'.$option);
            if (Str::startsWith($option, '__') || in_array($setMethod, self::$protectedMethods)) {
                return;
            }
            if (! method_exists($this, $setMethod)) {
                return;
            }

            call_user_func_array([$this, $setMethod], [$value]);
        }
    }

    /**
     * Retrieves a directive's argument contents.
     *
     * @param  DirectiveNode  $node The directive node.
     */
    protected function getDirectiveArgContents(DirectiveNode $node): string
    {
        $content = '';

        if ($node->hasArguments()) {
            $content = $node->arguments->innerContent;
        }

        return trim(StringUtilities::unwrapParentheses($content));
    }

    /**
     * Helper method to construct a new instance of `ValidationResult` with basic details.
     *
     * @param  AbstractNode  $subject The node.
     * @param  string  $message The validation result message.
     */
    protected function makeValidationResult(AbstractNode $subject, string $message = ''): ValidationResult
    {
        $result = new ValidationResult($subject, $message);
        $result->createdFromValidatorClass = get_class($this);

        return $result;
    }
}
