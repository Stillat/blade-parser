<?php

use Stillat\BladeParser\Support\Utilities\Paths;


test('null paths return null', function () {
    expect(Paths::normalizePath(null))->toBeNull();
    expect(Paths::normalizePathWithTrailingSlash(null))->toBeNull();
});

test('trailing slash is not duplicated', function () {
    expect(Paths::normalizePathWithTrailingSlash('/path/'))->toBe('/path/');
});

test('trailing slash is added', function () {
    expect(Paths::normalizePathWithTrailingSlash('/path'))->toBe('/path/');
});

test('backslashes are converted', function () {
    expect(Paths::normalizePathWithTrailingSlash('\\mnt\\c\\wsl'))->toBe('/mnt/c/wsl/');
});
