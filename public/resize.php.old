<?php

// load the Composer autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load the .env file
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Get the URL from the query string
$image_url = $_GET['url'];

if(!$image_url) {
    // The domain is not allowed, reject the URL
    header("HTTP/1.1 500 Internal Server Error");
    echo 'Error: No URL provided';
}

// Parse the URL to get the domain
$domain = parse_url($image_url, PHP_URL_HOST);

// Check if the domain is in the allowed list
$allowed_domains = explode(',', $_ENV['ALLOWED_DOMAINS']);

// Check if domain is allowed
if (in_array($domain, $allowed_domains)) {

    // Define cache lifetime
    header('Cache-Control: max-age=' . $_ENV['CACHE_LIFETIME']);

    // Get the width, height, quality, and crop from the querystring
    $image_width = $_GET['width'];
    $image_height = $_GET['height'];
    $image_quality = $_GET['quality'];
    $image_crop = $_GET['crop'];

    // Set quality default
    if(!$image_quality)
        $image_quality = 100;

    // Generate a cache key based on the querystring parameters
    $cache_key = md5($image_url . '_' . $image_width . '_' . $image_height . '_' . $image_quality . '_' . $image_crop);

    // Check if the image is already in the cache
    $cache_dir = $_ENV['CACHE_DIRECTORY'];
    $cache_file = $cache_dir . $cache_key;

    // Get the image type
    $image_type = getImageType(file_get_contents($cache_file));

    // Check if image is in cache and hasn't expired
    if (file_exists($cache_file) and getFileAgeInSeconds($cache_file) <= $_ENV['CACHE_LIFETIME']) {
        // If it is, include it in the response and exit
        header("Content-Type: image/$image_type");
        header('Image-Cached: cached');
        readfile($cache_file);
        exit();
    } else {
        header('Image-Cached: not-cached');
    }

    // If the image is not in the cache, download it, crop it (if necessary), resize it, and save it in the cache

    // Get image type
    $image_raw = file_get_contents($image_url);
    $image_type = getImageType($image_raw);

    // Throw error if unsupported image type
    if(!in_array($image_type,['jpeg','png']))
    {
        // The domain is not allowed, reject the URL
        header("HTTP/1.1 500 Internal Server Error");
        echo 'Error: Unsupported image type - ' . $image_type;
        exit();
    }

    // Create an image object from the raw data
    $image = imagecreatefromstring($image_raw);

    // If PNG then process transparency data into image object
    if($image_type == 'png') {
        imageAlphaBlending($image, true);
        imageSaveAlpha($image, true);
    }

    // Get the original image dimensions
    $original_width = imagesx($image);
    $original_height = imagesy($image);

    // Handle instance where width and height not provided
    if(!$image_width && !$image_height) {
        $image_width = $original_width;
        $image_height = $original_height;
    }

    // Calculate original aspect ratio
    $original_aspect_ratio = $original_width / $original_height;

    // Handle missing width or height
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

        // Crop appropriately depending on image type
        if($image_type == 'jpeg') {

            $cropped_image = imagecrop($image, ['x' => $crop_x, 'y' => $crop_y, 'width' => $new_width, 'height' => $new_height]);

        } else if($image_type == 'png') {

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

    // Resize the image, save the image in the cache and return it
    if ($image_type == 'jpeg') {

        // Resize the image
        $resized_image = imagecreatetruecolor($image_width, $image_height);

        // Copy and resize the original image to the new image
        imagecopyresampled($resized_image, $image, 0, 0, 0, 0, $image_width, $image_height, $new_width, $new_height);

        // Return the image in the response and save to cache
        imagejpeg($resized_image, $cache_file, $image_quality);

        // Include the image in the response
        header('Content-Type: image/jpeg');
        imagejpeg($resized_image);

    } else if ($image_type == 'png') {

        // Create a new transparent PNG image with the new dimensions
        $resized_image = imagecreatetruecolor($image_width, $image_height);
        imagesavealpha($resized_image, true);
        $trans_color = imagecolorallocatealpha($resized_image, 0, 0, 0, 127);
        imagefill($resized_image, 0, 0, $trans_color);

        // Copy and resize the original image to the new image
        imagecopyresampled($resized_image, $image, 0, 0, 0, 0, $image_width, $image_height, $new_width, $new_height);

        //Return the image in the response and save to cache
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


/**
 * Gets image type from an image string
 */
function getImageType($image) {
    if (is_string($image) && !empty($image)) {

        // Get image info
        $image_info = getimagesizefromstring($image);

        if ($image_info !== false) {

            // Hook out type info
            $image_type = $image_info[2];
            if ($image_type == IMAGETYPE_JPEG) {
                return 'jpeg';
            } elseif ($image_type == IMAGETYPE_GIF) {
                return 'gif';
            } elseif ($image_type == IMAGETYPE_PNG) {
                return 'png';
            } else {
                return 'unknown';
            }
        } else {
            return 'not an image';
        }
    } else {
        return 'invalid input';
    }
}

/**
 * Gets age of file in seconds
 */
function getFileAgeInSeconds($file_path) {
    if (file_exists($file_path)) {
        return time() - filemtime($file_path);
    } else {
        return false;
    }
}
