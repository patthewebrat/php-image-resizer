# Image Resize and Cache Script

This is a PHP script that takes in an image URL, width, height, quality, and crop via the query string, resizes and crops the image (if necessary), optimizes it (if it's a JPEG), saves the image in a cache, and includes the image in the response.

It can handle jpeg and png files. It will only optimise jpg files.

## Installation

To download vendor files run:

`composer install`

Then create .env and edit this file to include your whitelisted image domains and cache lifetime (in seconds)

`cp .env.example .env`

Point the web root to the public directory and you should be good to go.

Should run on most versions of PHP, has been tested up to 8.1. You'll probably need to increase the memory limit upwards of 512M if you are resizing larger images.

## Usage

To use this script, you can simply call the script with the following query string parameters:

* url (required): the URL of the image to be resized and cached
* width (optional): the width of the resized image in pixels
* height (optional): the height of the resized image in pixels
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

Example URL: http://example.com/resize?url=http://example.com/image.jpg&width=200&height=200&quality=80&crop=bottomright

This script only resizes jpg and png images currently.

## Cache

The script will save the resized and optimized image in a cache folder to speed up subsequent requests. The cache key is generated based on the query string parameters, so if the same URL, width, height, quality, and crop are requested again, the cached image will be returned.
License

You can clear the cache at any time by calling https://example.com/clear, or by running

`php clear.php` 

in the public directory.

This script is released under the MIT License. You are free to use, modify, and distribute this script as you wish.

