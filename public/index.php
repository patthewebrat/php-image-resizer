<?php

declare(strict_types=1);

// Bootstrap the application
$container = require_once __DIR__ . '/../src/bootstrap.php';

// Get the resize controller
$controller = $container['resizeController'];

// Handle the request
$controller->handle($_GET);
