# Standalone Blade Parser and Compiler

This is an experimental Blade parser and compiler. It is able to parse Blade templates, and regenerate them.

## Parsing a Blade Document

To parse a document, first create an instance of the Blade parser:

```php
use Stillat\BladeParser\Parsers\Blade;

$parser = new Blade();

```

Once you have a parser instance, you may use the `parse($input)` method to receive an instance of `Stillat\BladeParser\Documents\Template`:

```php
use Stillat\BladeParser\Parsers\Blade;

$parser = new Blade();

$document = $parser->parse('@extends("layout")');

```

The `Template` document instance contains information about the parsed Blade template. To work with the document, you must implement the `Stillat\BladeParser\Visitors\AbstractNodeVisitor` interface:

```php

use Stillat\BladeParser\Nodes\Node;
use Stillat\BladeParser\Visitors\AbstractNodeVisitor;

class MyVisitor extends AbstractNodeVisitor
{

    public function onEnter(Node $node)
    {
        echo $node->innerContent;
    }
}


```

Once you have created your visitor, you must ask the document for a `TemplateTraverser` instance, add your new visitor, and then call the `traverse()` method:

```php
$traverser = $document->getTraverser();
$traverser->addVisitor(new MyVisitor());

$traverser->traverse();
```

The `onEnter` method within all added traversers will be invoked with the document's nodes.

The built-in `Stillat\BladeParser\Visitors\PrinterNodeVisitor` and the `Stillat\BladeParser\Printers\Php\Printer` demonstrate advanced usage of the traverser mechanisms.

## Component Details Resolver

The provided PHP compiler (printer) requires an instance of `Stillat\BladeParser\Contracts\ComponentDetailsResovlerContract`. This interface is responsible for providing information about the component's class name, properties, data, and attributes. This resolver will be environment specific, you may use the mocked test resolver at `tests/AppComponentNameFinder.php` as an example.

## Using the Default PHP Compiler

To compile a Blade document from a collection of previously parsed nodes, you will first need to create an instance of `Stillat\BladeParser\Contracts\ComponentDetailsResovlerContract`. Once you have this interface implemented, you may set up a compiler like so:

```php
use Stillat\BladeParser\Compilers\PhpCompiler;

$compiler = new PhpCompiler($yourComponentDetailsResolverInstance);


$compiledString = $compiler->compileString('@extends("some_file")');

```

## Known Issues

Not all of the details provided in the `Node` instances are 100% accurate at this time. Pull requests are welcomed!

## License

This parser library is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
