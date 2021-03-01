<?php

require_once 'vendor/autoload.php';

$template = file_get_contents('template.blade.php');

$parser = new \Stillat\BladeParser\Parsers\Blade();

$template = "{{ 'I am Blade' }}"."\n@php echo 'I am PHP {{ not Blade }}' @endphp";
$doc = $parser->parse($template);

var_dump($doc->withPrinter(new \Stillat\BladeParser\Printers\Php\Printer())->getContents());
exit;
ray($doc->withPrinter(new \Stillat\BladeParser\Printers\Php\Printer())->getContents());
