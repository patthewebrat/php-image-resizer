<?php

declare(strict_types=1);

use ImageResizer\Exceptions\ImageProcessingException;
use ImageResizer\Services\ImageProcessor;

function createTestImage(int $width, int $height, string $type = 'jpeg'): string
{
    $image = imagecreatetruecolor($width, $height);

    if ($type === 'png') {
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $transparent);
    }

    // Add some color to make it a valid image
    $color = imagecolorallocate($image, 255, 0, 0);
    imagefilledrectangle($image, 0, 0, $width, $height, $color);

    ob_start();
    if ($type === 'jpeg') {
        imagejpeg($image, null, 90);
    } else {
        imagepng($image);
    }
    $data = ob_get_clean();

    imagedestroy($image);

    return $data;
}

test('can process and resize jpeg image', function () {
    $processor = new ImageProcessor();
    $imageData = createTestImage(200, 200, 'jpeg');

    [$resizedImage, $width, $height] = $processor->process($imageData, 'jpeg', 100, 100, 75);

    expect($width)->toBe(100)
        ->and($height)->toBe(100)
        ->and($resizedImage)->toBeInstanceOf(GdImage::class);

    imagedestroy($resizedImage);
});

test('can process and resize png image with transparency', function () {
    $processor = new ImageProcessor();
    $imageData = createTestImage(200, 200, 'png');

    [$resizedImage, $width, $height] = $processor->process($imageData, 'png', 100, 100, 75);

    expect($width)->toBe(100)
        ->and($height)->toBe(100)
        ->and($resizedImage)->toBeInstanceOf(GdImage::class);

    imagedestroy($resizedImage);
});

test('maintains aspect ratio when only width is provided', function () {
    $processor = new ImageProcessor();
    $imageData = createTestImage(200, 100, 'jpeg');

    [$resizedImage, $width, $height] = $processor->process($imageData, 'jpeg', 100, null, 75);

    expect($width)->toBe(100)
        ->and($height)->toBe(50); // Half of width to maintain 2:1 aspect ratio

    imagedestroy($resizedImage);
});

test('maintains aspect ratio when only height is provided', function () {
    $processor = new ImageProcessor();
    $imageData = createTestImage(200, 100, 'jpeg');

    [$resizedImage, $width, $height] = $processor->process($imageData, 'jpeg', null, 50, 75);

    expect($width)->toBe(100) // Double of height to maintain 2:1 aspect ratio
        ->and($height)->toBe(50);

    imagedestroy($resizedImage);
});

test('uses original dimensions when width and height are null', function () {
    $processor = new ImageProcessor();
    $imageData = createTestImage(200, 100, 'jpeg');

    [$resizedImage, $width, $height] = $processor->process($imageData, 'jpeg', null, null, 75);

    expect($width)->toBe(200)
        ->and($height)->toBe(100);

    imagedestroy($resizedImage);
});

test('can crop image with centre position', function () {
    $processor = new ImageProcessor();
    $imageData = createTestImage(200, 200, 'jpeg');

    [$resizedImage, $width, $height] = $processor->process($imageData, 'jpeg', 100, 50, 75, 'centre');

    expect($width)->toBe(100)
        ->and($height)->toBe(50)
        ->and($resizedImage)->toBeInstanceOf(GdImage::class);

    imagedestroy($resizedImage);
});

test('can crop image with topleft position', function () {
    $processor = new ImageProcessor();
    $imageData = createTestImage(200, 200, 'jpeg');

    [$resizedImage, $width, $height] = $processor->process($imageData, 'jpeg', 100, 50, 75, 'topleft');

    expect($resizedImage)->toBeInstanceOf(GdImage::class);

    imagedestroy($resizedImage);
});

test('can output jpeg image', function () {
    $processor = new ImageProcessor();
    $imageData = createTestImage(100, 100, 'jpeg');

    [$image] = $processor->process($imageData, 'jpeg', 50, 50, 75);
    $output = $processor->outputImage($image, 'jpeg', 75);

    expect($output)->toBeString()
        ->and(strlen($output))->toBeGreaterThan(0);

    imagedestroy($image);
});

test('can output png image', function () {
    $processor = new ImageProcessor();
    $imageData = createTestImage(100, 100, 'png');

    [$image] = $processor->process($imageData, 'png', 50, 50, 75);
    $output = $processor->outputImage($image, 'png', 75);

    expect($output)->toBeString()
        ->and(strlen($output))->toBeGreaterThan(0);

    imagedestroy($image);
});

test('throws exception for invalid image data', function () {
    $processor = new ImageProcessor();

    $processor->process('not an image', 'jpeg', 100, 100, 75);
})->throws(ImageProcessingException::class);

test('throws exception for unsupported output type', function () {
    $processor = new ImageProcessor();
    $imageData = createTestImage(100, 100, 'jpeg');

    [$image] = $processor->process($imageData, 'jpeg', 50, 50, 75);

    try {
        $processor->outputImage($image, 'gif', 75);
    } finally {
        imagedestroy($image);
    }
})->throws(ImageProcessingException::class, 'Unsupported output type');
