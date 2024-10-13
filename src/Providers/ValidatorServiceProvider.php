<?php

namespace Stillat\BladeParser\Providers;

use Illuminate\Support\ServiceProvider;
use Stillat\BladeParser\Validation\AbstractDocumentValidator;
use Stillat\BladeParser\Validation\AbstractNodeValidator;
use Stillat\BladeParser\Validation\BladeValidator;
use Stillat\BladeParser\Validation\Validators\Concerns\AcceptsCustomDirectives;
use Stillat\BladeParser\Validation\Validators\Concerns\CanIgnoreDirectives;
use Stillat\BladeParser\Validation\Validators\Documents\InvalidPhpDocumentValidator;
use Stillat\BladeParser\Validation\Validators\DuplicateConditionExpressionsValidator;
use Stillat\BladeParser\Validation\Workspaces\PhpStanWrapper;

class ValidatorServiceProvider extends ServiceProvider
{
    public static function getIgnoreDirectives()
    {
        return array_merge(config('blade.validation.ignore_directives', []), ['livewire']);
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/validation.php', 'blade.validation'
        );

        $globalIgnoreDirectives = self::getIgnoreDirectives();
        $globalCustomDirectives = config('blade.validation.custom_directives', []);

        $availableConfig = config('blade.validation.options');

        $validators = config('blade.validation.core_validators', []);

        if (PhpStanWrapper::canRun()) {
            $b4 = $validators;
            // Remove some core validators that will be duplicated by some errors.
            $validators = array_diff($validators, [
                DuplicateConditionExpressionsValidator::class,
                InvalidPhpDocumentValidator::class,
            ]);
        }

        foreach ($validators as $validator) {
            $this->app->singleton($validator, function () use ($validator, $availableConfig, $globalIgnoreDirectives, $globalCustomDirectives) {
                /** @var AbstractNodeValidator $instance */
                $instance = new $validator;
                $options = [];

                if (array_key_exists($validator, $availableConfig)) {
                    $options = $availableConfig[$validator];
                }

                if (class_uses($instance, CanIgnoreDirectives::class)) {
                    if (array_key_exists('ignore_directives', $options)) {
                        $options['ignore_directives'] = array_merge($globalIgnoreDirectives, $options['ignore_directives']);
                    } else {
                        $options['ignore_directives'] = $globalIgnoreDirectives;
                    }
                }

                if (class_uses($instance, AcceptsCustomDirectives::class)) {
                    if (array_key_exists('custom_directives', $options)) {
                        $options['custom_directives'] = array_merge($globalCustomDirectives, $options['custom_directives']);
                    } else {
                        $options['custom_directives'] = $globalCustomDirectives;
                    }
                }

                $instance->loadConfiguration($options);

                return $instance;
            });
        }
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/validation.php' => config_path('blade/validation.php'),
        ], ['blade']);

        $this->app->resolving(BladeValidator::class, function (BladeValidator $validator) {
            $coreValidators = collect(config('blade.validation.core_validators', []))->map(fn (string $class) => app($class))->all();

            $nodeValidators = [];
            $documentValidators = [];

            foreach ($coreValidators as $instance) {
                if ($instance instanceof AbstractDocumentValidator) {
                    $documentValidators[] = $instance;
                } else {
                    $nodeValidators[] = $instance;
                }
            }

            $validator->setCoreValidatorInstances($nodeValidators);
            $validator->setCoreDocumentValidatorInstances($documentValidators);
        });
    }
}
