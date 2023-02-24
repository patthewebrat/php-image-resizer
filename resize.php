<?php

// Get the image URL, width, height, quality, and crop from the querystring
$image_url = $_GET['url'];
$image_width = $_GET['width'];
$image_height = $_GET['height'];
$image_quality = $_GET['quality'];
$image_crop = $_GET['crop'];

// Generate a cache key based on the querystring parameters
$cache_key = md5($image_url . '_' . $image_width . '_' . $image_height . '_' . $image_quality . '_' . $image_crop);

// Check if the image is already in the cache
$cache_dir = './cache/';
$cache_file = $cache_dir . $cache_key;

if (file_exists($cache_file)) {
    // If it is, include it in the response and exit
    header('Content-Type: image/jpeg');
    header('Indulge-cached: cached');
    readfile($cache_file);
    exit();
} else {
    header('Indulge-cached: not-cached');
}

// If the image is not in the cache, download it, crop it (if necessary), resize it, and save it in the cache
$image_data = file_get_contents($image_url);

$image = imagecreatefromstring($image_data);

// Get the original image dimensions
$original_width = imagesx($image);
$original_height = imagesy($image);

// Calculate the new image dimensions while maintaining the aspect ratio of the original image
$aspect_ratio = $image_width / $image_height;
//$aspect_ratio = $original_width / $original_height;
if ($original_width / $original_height > $aspect_ratio) {
    $new_width = $original_height * $aspect_ratio;
    $new_height = $original_height;
} else {
    $new_width = $original_width;
    $new_height = $original_width / $aspect_ratio;
}

// Crop the image if necessary
if ($original_width / $original_height != $aspect_ratio) {
    switch ($image_crop) {
        case 'topleft':
            $crop_x = 0;
            $crop_y = 0;
            break;
        case 'topright':
            $crop_x = $original_width - $new_width;
            $crop_y = 0;
            break;
        case 'bottomleft':
            $crop_x = 0;
            $crop_y = $original_height - $new_height;
            break;
        case 'bottomright':
            $crop_x = $original_width - $new_width;
            $crop_y = $original_height - $new_height;
            break;
        case 'centre':
            $crop_x = ($original_width - $new_width) / 2;
            $crop_y = ($original_height - $new_height) / 2;
            break;
        default:
            $crop_x = 0;
            $crop_y = 0;
            break;
    }
    $cropped_image = imagecrop($image, ['x' => $crop_x, 'y' => $crop_y, 'width' => $new_width, 'height' => $new_height]);
    imagedestroy($image);
    $image = $cropped_image;
}

// Resize the image
$resized_image = imagescale($image, $image_width, $image_height);

// Save the image in the cache (optimising it if it is a JPEG)
if (strpos($image_url, '.jpg') !== false || strpos($image_url, '.jpeg') !== false) {
    imagejpeg($resized_image, $cache_file, $image_quality);
} else {
    imagepng($resized_image, $cache_file);
}

// Free up memory
imagedestroy($resized_image);
imagedestroy($image);

// Include the image in the response
header('Content-Type: image/jpeg');
imagejpeg($resized_image);
