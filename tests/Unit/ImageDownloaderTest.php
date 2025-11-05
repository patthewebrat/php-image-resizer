<?php

declare(strict_types=1);

use ImageResizer\Exceptions\InvalidImageException;
use ImageResizer\Services\DomainValidator;
use ImageResizer\Services\ImageDownloader;

test('can detect jpeg image type', function () {
    $config = getTestConfig();
    $validator = new DomainValidator($config);
    $downloader = new ImageDownloader($config, $validator);

    // Create a minimal JPEG
    $jpeg = "\xFF\xD8\xFF\xE0\x00\x10\x4A\x46\x49\x46";

    expect($downloader->getImageType($jpeg))->toBe('jpeg');
});

test('can detect png image type', function () {
    $config = getTestConfig();
    $validator = new DomainValidator($config);
    $downloader = new ImageDownloader($config, $validator);

    // PNG header
    $png = "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A";

    expect($downloader->getImageType($png))->toBe('png');
});

test('returns invalid for non-image data', function () {
    $config = getTestConfig();
    $validator = new DomainValidator($config);
    $downloader = new ImageDownloader($config, $validator);

    expect($downloader->getImageType('not an image'))->toBe('invalid');
});

test('returns supported types', function () {
    $config = getTestConfig();
    $validator = new DomainValidator($config);
    $downloader = new ImageDownloader($config, $validator);

    expect($downloader->getSupportedTypes())->toBe(['jpeg', 'png']);
});
