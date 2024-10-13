<?php

uses(\Stillat\BladeParser\Tests\ParserTestCase::class);
use Stillat\BladeParser\Compiler\CompilerServices\LoopVariablesExtractor;

beforeEach(function () {
    $this->extractor = new LoopVariablesExtractor;
});

test('basic loop variable extraction', function () {
    $input = '($users as $user)';
    $result = $this->extractor->extractDetails($input);

    expect($result)->not->toBeNull();
    expect($result->source)->toBe('($users as $user)');
    expect($result->variable)->toBe('$users');
    expect($result->alias)->toBe('$user');
    expect($result->isValid)->toBe(true);
});

test('nested strings and keywords dont confuse things', function () {
    $input = 'explode(", ", "as,as,as,as") as $as';
    $result = $this->extractor->extractDetails($input);

    expect($result->variable)->toBe('explode(", ", "as,as,as,as")');
    expect($result->alias)->toBe('$as');
    expect($result->isValid)->toBeTrue();
});

test('invalid loop variables', function ($input) {
    $result = $this->extractor->extractDetails($input);
    expect($result->isValid)->toBeFalse();
})->with('invalidLoopVariableSources');

dataset('invalidLoopVariableSources', function () {
    return [
        ['as'],
        ['as $user'],
        ['$users as   '],
    ];
});
