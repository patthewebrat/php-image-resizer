<?php

declare(strict_types=1);

namespace ImageResizer\Controllers;

use ImageResizer\Services\CacheService;

class ClearCacheController
{
    public function __construct(
        private readonly CacheService $cacheService
    ) {
    }

    public function handle(): void
    {
        $count = $this->cacheService->clear();

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Cache cleared',
            'files_deleted' => $count,
        ]);
    }
}
