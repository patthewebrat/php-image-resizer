# Image Resize and Cache Script

This is a PHP script that takes in an image URL, width, height, quality, and crop via the query string, resizes and crops the image (if necessary), optimizes it (if it's a JPEG), saves the image in a cache, and includes the image in the response.

## Installation

To download vendor files run:

`composer install`

Then create .env and edit this file to include your whitelisted image domains.

`cp .env.example .env`

Point the web root to the public directory and you should be good to go.

## Usage

To use this script, you can simply call the script with the following query string parameters:

* url (required): the URL of the image to be resized and cached
* width (required): the width of the resized image in pixels
* height (required): the height of the resized image in pixels
* quality (optional): the quality of the output image (0-100), only applicable for JPEG images (default: 75)
* crop (optional): the cropping option to be used to crop the image to the specified aspect ratio. Supported values are:
    * topleft: image is cropped from the top left down
    * topright: image is cropped from the top right down
    * bottomleft: image is cropped from the bottom left up
    * bottomright: image is cropped from the bottom right up
    * bottomcentre: image is cropped from the center horizontally, then bottom up vertically
    * topcentre: image is cropped from the center horizontally, then top down vertically
    * centreleft: image is cropped from the center vertically, then left to right horizontally
    * centreright: image is cropped from the center vertically, then right to left horizontally
    * centre: image is cropped from the center outwards

Example URL: http://yourdomain.com/resize?url=http://example.com/image.jpg&width=200&height=200&quality=80&crop=bottomright

## Cache

The script will save the resized and optimized image in a cache folder to speed up subsequent requests. The cache key is generated based on the query string parameters, so if the same URL, width, height, quality, and crop are requested again, the cached image will be returned.
License

You can clear the cache at any time by calling https://yourdomain.com/clear, or by running

`php clear.php`

in the public directory.

This script is released under the MIT License. You are free to use, modify, and distribute this script as you wish.
