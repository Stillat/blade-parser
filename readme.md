![](.art/banner.png)

Blade Parser is library for Laravel that makes it easy to parse, analyze, and manipulate Blade templates.

The library is composed of many major components:

* **Parser**: A Blade parser that produces a list of nodes, which can be analyzed to help make decisions surrounding a template.
* **Documents**: A powerful abstraction that makes it much simpler to interact with the details of a single parsed Blade template.
* **Workspaces**: A simple set of APIs that make it effortless to work with multiple parsed Blade templates at once.
* **Compiler**: A highly configurable Blade compiler implementation, with support for precompilers, extensions, and existing third-party packages.
* **Validator**: An extensible system for validating Blade documents, with default validators capable of detecting unpaired conditions, invalid component parameter spacing, and much more.

## Simple to Use

Parsing Blade templates is incredibly simple using the Documents API. As an example, this is all that is needed to parse a template:

```php
<?php
 
use Stillat\BladeParser\Document\Document;
 
$template = <<<'BLADE'
    Hello, {{ $world }}
BLADE;
 
$document = Document::fromText($template);
```

The `Document` class provides a powerful abstraction, making it simple to quickly retrieve information about a template.

For instance, if we wanted to extract all the components from our template we could do this:

```php
<?php

// Do something with all component tags in the template.
$document->getComponents()
          ->each(fn($node) => ...);
```

If we were only interested in a component named `alert`, we could instead use:

```php
<?php

// Find all "alert" components.
$document->findComponentsByTagName('alert')
         ->each(fn($node) => ...);
```

These examples hardly scratch the surface, and you are encouraged to read through the [Documentation](https://bladeparser.com/).

## Built-in Validation Command

This library also ships with a configurable `blade:validate` Artisan command which can be used to validate all Blade templates within a project.

To configure the command, you will need to publish its configuration files using the following command:

```bash
php artisan vendor:publish --tag=blade
```

To run the validation against your project, you can issue the following Artisan command:

```bash
php artisan blade:validate
```

If any validation issues were detected they will be displayed in your terminal.

There are many configuration options available, and if you'd like to learn more you can find them documented in the [Configuring the Validate Command](https://bladeparser.com/docs/v1/the-validate-command#configuring-the-validate-command) article.

## License

This parser library is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
