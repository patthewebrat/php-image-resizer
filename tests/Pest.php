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
        'default_quality' => 100, // Backward compatibility with old default
        'max_file_size' => 10485760,
        'request_timeout' => 30,
    ];

    return new Config(array_merge($defaults, $override));
}

/**
 * Create a test image for testing
 */
function createTestImage(int $width, int $height, string $type = 'jpeg'): string
{
    $image = imagecreatetruecolor($width, $height);
    if ($image === false) {
        throw new RuntimeException('Failed to create test image');
    }

    if ($type === 'png') {
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        if ($transparent !== false) {
            imagefill($image, 0, 0, $transparent);
        }
    }

    // Add some color to make it a valid image
    $color = imagecolorallocate($image, 255, 0, 0);
    if ($color !== false) {
        imagefilledrectangle($image, 0, 0, $width, $height, $color);
    }

    ob_start();
    if ($type === 'jpeg') {
        imagejpeg($image, null, 90);
    } else {
        imagepng($image);
    }
    $data = ob_get_clean();

    imagedestroy($image);

    return $data !== false ? $data : '';
}
