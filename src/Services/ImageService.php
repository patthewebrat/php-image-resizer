<?php

declare(strict_types=1);

namespace ImageResizer\Services;

use GdImage;
use ImageResizer\Config\Config;

class ImageService
{
    public function __construct(
        private readonly Config $config,
        private readonly ImageDownloader $downloader,
        private readonly ImageProcessor $processor,
        private readonly CacheService $cache
    ) {
    }

    public function resize(
        string $url,
        ?int $width,
        ?int $height,
        ?int $quality = null,
        ?string $crop = null
    ): array {
        // Use default quality if not provided
        $quality = $quality ?? $this->config->getDefaultQuality();

        // Generate cache key
        $cacheKey = $this->cache->generateKey($url, $width, $height, $quality, $crop);

        // Check cache
        $cached = $this->cache->get($cacheKey);
        if ($cached !== false) {
            $imageType = $this->downloader->getImageType($cached);
            return [
                'data' => $cached,
                'type' => $imageType,
                'cached' => true,
            ];
        }

        // Download image
        $imageData = $this->downloader->download($url);
        $imageType = $this->downloader->getImageType($imageData);

        // Process image
        [$processedImage, $finalWidth, $finalHeight] = $this->processor->process(
            $imageData,
            $imageType,
            $width,
            $height,
            $quality,
            $crop
        );

        // Output to string and cache
        $outputData = $this->processor->outputImage($processedImage, $imageType, $quality);
        $this->cache->put($cacheKey, $outputData);

        // Clean up
        imagedestroy($processedImage);

        return [
            'data' => $outputData,
            'type' => $imageType,
            'cached' => false,
        ];
    }
}
