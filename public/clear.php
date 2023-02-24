<?php
// Specify the cache directory
$cache_dir = '../cache/';

// Get all the cache files
$cache_files = glob($cache_dir . '*');

// Loop through the cache files and delete them
foreach ($cache_files as $cache_file) {
    if (is_file($cache_file)) {
        unlink($cache_file);
    }
}

// Return a message indicating that the cache has been cleared
echo 'Cache cleared';