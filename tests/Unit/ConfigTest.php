<?php

declare(strict_types=1);

use ImageResizer\Config\Config;

test('can create config with custom values', function () {
    $config = getTestConfig();

    expect($config->getAllowedDomains())->toBe(['example.com', 'test.com'])
        ->and($config->getCacheLifetime())->toBe(3600)
        ->and($config->getDefaultQuality())->toBe(75)
        ->and($config->getMaxFileSize())->toBe(10485760)
        ->and($config->getRequestTimeout())->toBe(30);
});

test('throws exception when allowed domains is empty', function () {
    new Config([
        'allowed_domains' => [],
        'cache_directory' => __DIR__ . '/../fixtures/cache/',
        'cache_lifetime' => 3600,
    ]);
})->throws(RuntimeException::class, 'ALLOWED_DOMAINS must be configured');

test('throws exception when cache directory is empty', function () {
    new Config([
        'allowed_domains' => ['example.com'],
        'cache_directory' => '',
        'cache_lifetime' => 3600,
    ]);
})->throws(RuntimeException::class, 'CACHE_DIRECTORY must be configured');

test('throws exception when cache lifetime is zero or negative', function () {
    new Config([
        'allowed_domains' => ['example.com'],
        'cache_directory' => __DIR__ . '/../fixtures/cache/',
        'cache_lifetime' => 0,
    ]);
})->throws(RuntimeException::class, 'CACHE_LIFETIME must be greater than 0');

test('normalizes cache directory path', function () {
    $config = getTestConfig();
    $path = $config->getCacheDirectory();

    expect($path)->toEndWith('/');
});

test('creates cache directory if it does not exist', function () {
    $tempDir = sys_get_temp_dir() . '/test_cache_' . uniqid();

    $config = new Config([
        'allowed_domains' => ['example.com'],
        'cache_directory' => $tempDir,
        'cache_lifetime' => 3600,
    ]);

    expect(is_dir($tempDir))->toBeTrue();

    // Cleanup
    rmdir($tempDir);
});
