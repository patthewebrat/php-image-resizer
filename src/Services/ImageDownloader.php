<?php

declare(strict_types=1);

namespace ImageResizer\Services;

use ImageResizer\Config\Config;
use ImageResizer\Exceptions\ImageDownloadException;
use ImageResizer\Exceptions\InvalidImageException;

class ImageDownloader
{
    private const SUPPORTED_TYPES = ['jpeg', 'png'];

    public function __construct(
        private readonly Config $config,
        private readonly DomainValidator $domainValidator
    ) {
    }

    /**
     * @throws ImageDownloadException
     * @throws InvalidImageException
     */
    public function download(string $url): string
    {
        $this->domainValidator->validate($url);

        $context = stream_context_create([
            'http' => [
                'timeout' => $this->config->getRequestTimeout(),
                'user_agent' => 'PHP Image Resizer/1.0',
                'follow_location' => true,
                'max_redirects' => 3,
            ],
        ]);

        $imageData = @file_get_contents($url, false, $context);

        if ($imageData === false) {
            throw new ImageDownloadException("Failed to download image from: {$url}");
        }

        if (strlen($imageData) === 0) {
            throw new ImageDownloadException("Downloaded image is empty from: {$url}");
        }

        if (strlen($imageData) > $this->config->getMaxFileSize()) {
            throw new ImageDownloadException(
                "Image exceeds maximum file size of " . $this->config->getMaxFileSize() . " bytes"
            );
        }

        $this->validateImageData($imageData);

        return $imageData;
    }

    /**
     * @throws InvalidImageException
     */
    private function validateImageData(string $imageData): void
    {
        $imageType = $this->getImageType($imageData);

        if (!in_array($imageType, self::SUPPORTED_TYPES, true)) {
            throw new InvalidImageException("Unsupported image type: {$imageType}");
        }
    }

    public function getImageType(string $imageData): string
    {
        if (empty($imageData)) {
            return 'invalid';
        }

        $imageInfo = @getimagesizefromstring($imageData);

        if ($imageInfo === false) {
            return 'invalid';
        }

        return match ($imageInfo[2]) {
            IMAGETYPE_JPEG => 'jpeg',
            IMAGETYPE_PNG => 'png',
            IMAGETYPE_GIF => 'gif',
            default => 'unknown',
        };
    }

    public function getSupportedTypes(): array
    {
        return self::SUPPORTED_TYPES;
    }
}
