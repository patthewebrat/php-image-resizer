<?php

declare(strict_types=1);

use ImageResizer\Config\Config;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

uses()->beforeEach(function () {
    // Clean up test cache directory before each test
    $testCacheDir = __DIR__ . '/fixtures/cache/';
    if (is_dir($testCacheDir)) {
        $files = glob($testCacheDir . '*');
        if ($files !== false) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }
})->in('Unit', 'Integration');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/

/**
 * @param array<string, mixed> $override
 */
function getTestConfig(array $override = []): Config
{
    $defaults = [
        'allowed_domains' => ['example.com', 'test.com'],
        'cache_directory' => __DIR__ . '/fixtures/cache/',
        'cache_lifetime' => 3600,
        'default_quality' => 75,
        'max_file_size' => 10485760,
        'request_timeout' => 30,
    ];

    return new Config(array_merge($defaults, $override));
}
