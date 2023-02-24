<?php

// load the Composer autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load the .env file
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Get the URL from the query string
$image_url = $_GET['url'];

//Define whether it is a jpeg
$isJpg = strpos($image_url, '.jpg') !== false || strpos($image_url, '.jpeg') !== false;

if(!$image_url) {
    // The domain is not allowed, reject the URL
    header("HTTP/1.1 500 Internal Server Error");
    echo 'Error: No URL provided';
}


// Parse the URL to get the domain
$domain = parse_url($image_url, PHP_URL_HOST);

// Check if the domain is in the allowed list
$allowed_domains = explode(',', $_ENV['ALLOWED_DOMAINS']);

if (in_array($domain, $allowed_domains)) {
    // The domain is allowed, proceed

    //Define cache lifetime
    header('Cache-Control: max-age=' . $_ENV['CACHE_LIFETIME']);

    // Get the width, height, quality, and crop from the querystring
    $image_width = $_GET['width'];
    $image_height = $_GET['height'];
    $image_quality = $_GET['quality'];
    $image_crop = $_GET['crop'];

    //Set quality default
    if(!$image_quality)
        $image_quality = 100;

    // Generate a cache key based on the querystring parameters
    $cache_key = md5($image_url . '_' . $image_width . '_' . $image_height . '_' . $image_quality . '_' . $image_crop);

    // Check if the image is already in the cache
    $cache_dir = '../cache/';
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
    if($isJpg) {
        $image = imagecreatefromjpeg($image_url);
    } else {
        $image = imagecreatefrompng($image_url);
        imageAlphaBlending($image, true);
        imageSaveAlpha($image, true);
    }

    // Get the original image dimensions
    $original_width = imagesx($image);
    $original_height = imagesy($image);

    //Handle instance where width and height not provided
    if(!$image_width && !$image_height) {
        $image_width = $original_width;
        $image_height = $original_height;
    }

    //Calculate original aspect ratio
    $original_aspect_ratio = $original_width / $original_height;

    //Handle missing width or height
    if(!$image_width)
        $image_width = $original_aspect_ratio * $image_height;

    if(!$image_height)
        $image_height = $image_width / $original_aspect_ratio;

    // Calculate the new image dimensions while maintaining the aspect ratio of the original image
    $aspect_ratio = $image_width / $image_height;

    if ($original_aspect_ratio > $aspect_ratio) {
        $new_width = $original_height * $aspect_ratio;
        $new_height = $original_height;
    } else {
        $new_width = $original_width;
        $new_height = $original_width / $aspect_ratio;
    }

    // Crop the image if necessary
    if ($original_aspect_ratio != $aspect_ratio) {
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
            case 'bottomcentre':
                $crop_x = ($original_width - $new_width) / 2;
                $crop_y = $original_height - $new_height;
                break;
            case 'topcentre':
                $crop_x = ($original_width - $new_width) / 2;
                $crop_y = 0;
                break;
            case 'centreleft':
                $crop_x = 0;
                $crop_y = ($original_height - $new_height) / 2;
                break;
            case 'centreright':
                $crop_x = $original_width - $new_width;
                $crop_y = ($original_height - $new_height) / 2;
                break;
            case 'centre': //Flips into default
            default:
                $crop_x = ($original_width - $new_width) / 2;
                $crop_y = ($original_height - $new_height) / 2;
                break;
        }

        if($isJpg) {

            $cropped_image = imagecrop($image, ['x' => $crop_x, 'y' => $crop_y, 'width' => $new_width, 'height' => $new_height]);

        } else {

            $cropped_image = imagecreatetruecolor($new_width, $new_height);
            imagesavealpha($cropped_image, true);
            $trans_color = imagecolorallocatealpha($cropped_image, 0, 0, 0, 127);
            imagefill($cropped_image, 0, 0, $trans_color);

            // Copy the cropped section from the original image to the new image
            imagecopy($cropped_image, $image, 0, 0, $crop_x, $crop_y, $new_width, $new_height);

        }

        imagedestroy($image); //Free up memory
        $image = $cropped_image;

    }

    // Save the image in the cache (optimising it if it is a JPEG)
    if ($isJpg) {

        // Resize the image
        $resized_image = imagecreatetruecolor($image_width, $image_height);

        // Copy and resize the original image to the new image
        imagecopyresampled($resized_image, $image, 0, 0, 0, 0, $image_width, $image_height, $new_width, $new_height);

        //Save to cache
        imagejpeg($resized_image, $cache_file, $image_quality);

        // Include the image in the response
        header('Content-Type: image/jpeg');
        imagejpeg($resized_image);

    } else {

        // Create a new transparent PNG image with the new dimensions
        $resized_image = imagecreatetruecolor($image_width, $image_height);
        imagesavealpha($resized_image, true);
        $trans_color = imagecolorallocatealpha($resized_image, 0, 0, 0, 127);
        imagefill($resized_image, 0, 0, $trans_color);

        // Copy and resize the original image to the new image
        imagecopyresampled($resized_image, $image, 0, 0, 0, 0, $image_width, $image_height, $new_width, $new_height);

        //Save to cache
        imagepng($resized_image, $cache_file);

        // Include the image in the response
        header('Content-Type: image/png');
        imagepng($resized_image);

    }

    // Free up memory
    imagedestroy($resized_image);
    imagedestroy($image);

} else {
    // The domain is not allowed, reject the URL
    header("HTTP/1.1 500 Internal Server Error");
    echo 'Error: Invalid domain';
}
