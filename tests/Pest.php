<?php

use Illuminate\Container\Container;
use Illuminate\Contracts\View\Factory;
use Stillat\BladeParser\Compiler\CompilerServices\CoreDirectiveRetriever;
use Stillat\BladeParser\Compiler\ComponentNodeCompiler;
use Stillat\BladeParser\Compiler\ComponentTagCompiler;
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Document\DocumentOptions;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Parser\DocumentParser;
use Stillat\BladeParser\Tests\Compiler\CustomTransformer;
use Mockery as m;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

// pest()->extend(Tests\TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * @param  ComponentNode[]  $components
 */
function assertManyComponentsArePaired(array $components): void
{
    $componentCount = count($components);
    $limit = $componentCount / 2;

    for ($i = 0; $i < $limit; $i++) {
        $closeIndex = $componentCount - ($i + 1);
        $openComponent = $components[$i];
        $closeComponent = $components[$closeIndex];

        test()->assertComponentsArePaired($openComponent, $closeComponent);
    }
}

/**
 * @param  DirectiveNode[]  $directives
 */
function assertManyDirectivesArePaired(array $directives): void
{
    $directiveCount = count($directives);
    $limit = $directiveCount / 2;

    for ($i = 0; $i < $limit; $i++) {
        $closeIndex = $directiveCount - ($i + 1);
        $openDirective = $directives[$i];
        $closeDirective = $directives[$closeIndex];

        test()->assertDirectivesArePaired($openDirective, $closeDirective, $openDirective->content.' <> ', $closeDirective->content);
    }
}

/**
 * @param  AbstractNode[]  $nodes
 */
function assertNodesHaveParent(DirectiveNode $parent, array $nodes): void
{
    foreach ($nodes as $node) {
        expect($node->parent)->toEqual($parent);
    }

    // Get rid of the final closing directive.
    /** @var DirectiveNode $closingDirective */
    $closingDirective = array_pop($nodes);

    test()->assertDirectivesArePaired($parent, $closingDirective);
    expect($parent->childNodes)->toHaveCount(count($nodes));

    // Make sure the children are in the correct order, and are the same instance.
    for ($i = 0; $i < count($nodes); $i++) {
        expect($parent->childNodes[$i])->toEqual($nodes[$i]);
    }
}

function directiveNames(): array
{
    return collect(CoreDirectiveRetriever::instance()->getDirectiveNames())->map(function ($s) {
        return [$s];
    })->values()->all();
}

function directiveNamesRequiringArguments(): array
{
    return collect(CoreDirectiveRetriever::instance()->getDirectivesRequiringArguments())->map(fn ($s) => [$s])->values()->all();
}

function directiveNamesWithoutArguments(): array
{
    return collect(CoreDirectiveRetriever::instance()->getDirectivesThatMustNotHaveArguments())->filter(fn ($s) => $s != 'verbatim' && $s != 'endverbatim')->map(fn ($s) => [$s])->values()->all();
}

function nonStructuralCoreDirectives(): array
{
    return collect(CoreDirectiveRetriever::instance()->getNonStructureDirectiveNames())->map(function ($name) {
        return [$name];
    })->all();
}

function coreDirectives(): array
{
    return collect(array_diff(CoreDirectiveRetriever::instance()->getDirectiveNames(), ['foreach', 'forelse', 'endverbatim', 'use']))->map(function ($name) {
        return ['@'.$name];
    })->all();
}

function mockViewFactory($existsSucceeds = true)
{
    $container = new Container;
    $container->instance(Factory::class, $factory = m::mock(Factory::class));
    $factory->shouldReceive('exists')->andReturn($existsSucceeds);
    Container::setInstance($container);
}

function transformDocument(string $template, bool $withCoreDirectives): string
{
    $doc = Document::fromText($template, documentOptions: new DocumentOptions(
        withCoreDirectives: $withCoreDirectives,
        customDirectives: ['custom', 'endcustom']
    ))->resolveStructures();

    return (new CustomTransformer())->transformDocument($doc);
}

function compiler(array $aliases = [], array $namespaces = []): ComponentTagCompiler
{
    $compiler = new ComponentTagCompiler(
        new ComponentNodeCompiler(),
        new DocumentParser()
    );

    $compiler->setAliases($aliases);
    $compiler->setNamespaces($namespaces);

    return $compiler;
}
