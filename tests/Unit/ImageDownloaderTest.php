<?php

declare(strict_types=1);

use ImageResizer\Exceptions\InvalidImageException;
use ImageResizer\Services\DomainValidator;
use ImageResizer\Services\ImageDownloader;

test('can detect jpeg image type', function () {
    $config = getTestConfig();
    $validator = new DomainValidator($config);
    $downloader = new ImageDownloader($config, $validator);

    // Create a valid JPEG image
    $image = imagecreatetruecolor(10, 10);
    ob_start();
    imagejpeg($image, null, 90);
    $jpeg = ob_get_clean();
    imagedestroy($image);

    expect($downloader->getImageType($jpeg))->toBe('jpeg');
});

test('can detect png image type', function () {
    $config = getTestConfig();
    $validator = new DomainValidator($config);
    $downloader = new ImageDownloader($config, $validator);

    // Create a valid PNG image
    $image = imagecreatetruecolor(10, 10);
    ob_start();
    imagepng($image);
    $png = ob_get_clean();
    imagedestroy($image);

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
