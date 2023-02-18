<?php

use Stillat\BladeParser\Validation\Validators\ComponentParameterNameSpacingValidator;
use Stillat\BladeParser\Validation\Validators\ComponentShorthandVariableParameterValidator;
use Stillat\BladeParser\Validation\Validators\DebugDirectiveValidator;
use Stillat\BladeParser\Validation\Validators\DirectiveArgumentSpacingValidator;
use Stillat\BladeParser\Validation\Validators\DirectiveArgumentsSpanningLinesValidator;
use Stillat\BladeParser\Validation\Validators\DirectiveSpacingValidator;
use Stillat\BladeParser\Validation\Validators\Documents\InvalidPhpDocumentValidator;
use Stillat\BladeParser\Validation\Validators\DuplicateConditionExpressionsValidator;
use Stillat\BladeParser\Validation\Validators\EmptyConditionValidator;
use Stillat\BladeParser\Validation\Validators\ForElseStructureValidator;
use Stillat\BladeParser\Validation\Validators\InconsistentDirectiveCasingValidator;
use Stillat\BladeParser\Validation\Validators\InconsistentIndentationLevelValidator;
use Stillat\BladeParser\Validation\Validators\NoArgumentsValidator;
use Stillat\BladeParser\Validation\Validators\NodeCompilationValidator;
use Stillat\BladeParser\Validation\Validators\RecursiveIncludeValidator;
use Stillat\BladeParser\Validation\Validators\RequiredArgumentsValidator;
use Stillat\BladeParser\Validation\Validators\RequiresOpenValidator;
use Stillat\BladeParser\Validation\Validators\SwitchValidator;
use Stillat\BladeParser\Validation\Validators\UnpairedConditionValidator;

return [

    /*
    |--------------------------------------------------------------------------
    | Core Validators
    |--------------------------------------------------------------------------
    |
    | The validators in this list will be added to BladeValidator
    | instances automatically. You can disable validators from
    | running within the `blade:validate` command if you
    | comment out/remove them from this list.
    |
    */

    'core_validators' => [
        UnpairedConditionValidator::class,
        EmptyConditionValidator::class,
        RequiredArgumentsValidator::class,
        DirectiveArgumentSpacingValidator::class,
        NoArgumentsValidator::class,
        DuplicateConditionExpressionsValidator::class,
        ForElseStructureValidator::class,
        SwitchValidator::class,
        InconsistentDirectiveCasingValidator::class,
        RequiresOpenValidator::class,
        DirectiveSpacingValidator::class,
        DirectiveArgumentsSpanningLinesValidator::class,
        NodeCompilationValidator::class,
        InvalidPhpDocumentValidator::class,
        InconsistentIndentationLevelValidator::class,
        DebugDirectiveValidator::class,
        ComponentParameterNameSpacingValidator::class,
        ComponentShorthandVariableParameterValidator::class,
        RecursiveIncludeValidator::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | PHP Stan Integration
    |--------------------------------------------------------------------------
    |
    | You can disable the PHPStan or Larastan integration by
    | setting this value to "false". This integration is
    | only activated if the corresponding packages are
    | installed to your local project using Composer.
    |
    */
    'phpstan' => [
        'enabled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Global Ignore Directives
    |--------------------------------------------------------------------------
    |
    | Specify a list of directive names (without the `@`) that
    | should be ignored by *all* validators that support it.
    | You may still add specific directives to each of the
    | validators specific configurations to merge them.
    |
    */
    'ignore_directives' => [

    ],

    /*
    |--------------------------------------------------------------------------
    | Global Custom Directives
    |--------------------------------------------------------------------------
    |
    | Specify a list of directive names (without the `@`) that
    | should be included by *all* validators that support it.
    | You may still add specific directives to each of the
    | validators specific configurations to merge them.
    |
    */
    'custom_directives' => [

    ],

    /*
    |--------------------------------------------------------------------------
    | Validator Configuration
    |--------------------------------------------------------------------------
    |
    | You may configure each of the core validators
    | by modifying their options within this list.
    |
    */
    'options' => [
        DirectiveArgumentSpacingValidator::class => [
            'expected_spacing' => 1,
            'ignore_directives' => [],
        ],

        UnpairedConditionValidator::class => [
            'ignore_directives' => [],
        ],

        DuplicateConditionExpressionsValidator::class => [
            'ignore_directives' => [],
        ],

        EmptyConditionValidator::class => [
            'ignore_directives' => [],
        ],

        NoArgumentsValidator::class => [
            'ignore_directives' => [],
            'custom_directives' => [],
        ],

        RequiredArgumentsValidator::class => [
            'ignore_directives' => [],
            'custom_directives' => [],
        ],

        InconsistentDirectiveCasingValidator::class => [
            'ignore_directives' => [],
            'custom_directives' => [],
        ],

        RequiresOpenValidator::class => [
            'ignore_directives' => [],
            'custom_directives' => [],
        ],

        DirectiveSpacingValidator::class => [
            'ignore_directives' => [],
        ],

        DirectiveArgumentsSpanningLinesValidator::class => [
            'max_line_span' => 1,
            'ignore_directives' => [],
        ],

        NodeCompilationValidator::class => [
            'ignore_directives' => [],
        ],

        DebugDirectiveValidator::class => [
            'ignore_directives' => [],
            'custom_directives' => [],
        ],
    ],
];
