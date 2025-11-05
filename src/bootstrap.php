<?php

declare(strict_types=1);

use ImageResizer\Config\Config;
use ImageResizer\Controllers\ClearCacheController;
use ImageResizer\Controllers\ResizeController;
use ImageResizer\Services\CacheService;
use ImageResizer\Services\DomainValidator;
use ImageResizer\Services\ImageDownloader;
use ImageResizer\Services\ImageProcessor;
use ImageResizer\Services\ImageService;

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Initialize configuration
$config = new Config();

// Build dependency tree
$domainValidator = new DomainValidator($config);
$imageDownloader = new ImageDownloader($config, $domainValidator);
$imageProcessor = new ImageProcessor();
$cacheService = new CacheService($config);
$imageService = new ImageService($config, $imageDownloader, $imageProcessor, $cacheService);

// Create controllers
$resizeController = new ResizeController($config, $imageService);
$clearCacheController = new ClearCacheController($cacheService);

// Return container array
return [
    'config' => $config,
    'resizeController' => $resizeController,
    'clearCacheController' => $clearCacheController,
];
