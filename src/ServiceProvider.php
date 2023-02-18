<?php

namespace Stillat\BladeParser;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Stillat\BladeParser\Compiler\Compiler;
use Stillat\BladeParser\Console\Commands\ValidateBladeCommand;
use Stillat\BladeParser\Parser\DocumentParser;
use Stillat\BladeParser\Support\BladeCompilerDetailsFetcher;

class ServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        $this->commands([
            ValidateBladeCommand::class,
        ]);

        $this->app->resolving(DocumentParser::class, function (DocumentParser $parser) {
            $parser->setDirectiveNames(array_keys(Blade::getCustomDirectives()));
        });

        $this->app->resolving(Compiler::class, function (Compiler $compiler) {
            // Extract information from the core Blade compiler.

            /** @var BladeCompilerDetailsFetcher $fetcher */
            $fetcher = app(BladeCompilerDetailsFetcher::class);

            $compiler->setAnonymousComponentPaths($fetcher->getAnonymousComponentPaths());
            $compiler->setAnonymousComponentNamespaces($fetcher->getAnonymousComponentNamespaces());
            $compiler->setClassComponentAliases($fetcher->getClassComponentAliases());
            $compiler->setClassComponentNamespaces($fetcher->getClassComponentNamespaces());
            $compiler->setPrecompilers($fetcher->getPrecompilers());
            $compiler->setEchoHandlers($fetcher->getEchoHandlers());
            $compiler->setExtensions($fetcher->getExtensions());
            $compiler->setConditions($fetcher->getConditions());
        });
    }

    public function register()
    {
    }
}
