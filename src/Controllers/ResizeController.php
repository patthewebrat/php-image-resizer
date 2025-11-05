<?php

declare(strict_types=1);

namespace ImageResizer\Controllers;

use ImageResizer\Config\Config;
use ImageResizer\Exceptions\InvalidDomainException;
use ImageResizer\Services\ImageService;
use Throwable;

class ResizeController
{
    public function __construct(
        private readonly Config $config,
        private readonly ImageService $imageService
    ) {
    }

    public function handle(array $params): void
    {
        try {
            // Validate required parameters
            $url = $params['url'] ?? null;
            if (empty($url)) {
                $this->sendError(400, 'URL parameter is required');
                return;
            }

            // Get optional parameters
            $width = isset($params['width']) ? (int)$params['width'] : null;
            $height = isset($params['height']) ? (int)$params['height'] : null;
            $quality = isset($params['quality']) ? (int)$params['quality'] : null;
            $crop = $params['crop'] ?? null;

            // Validate quality range
            if ($quality !== null && ($quality < 1 || $quality > 100)) {
                $this->sendError(400, 'Quality must be between 1 and 100');
                return;
            }

            // Process the image
            $result = $this->imageService->resize($url, $width, $height, $quality, $crop);

            // Send response
            $this->sendSuccess($result);
        } catch (InvalidDomainException $e) {
            $this->sendError(403, 'Domain not allowed');
        } catch (Throwable $e) {
            error_log('Image resize error: ' . $e->getMessage());
            $this->sendError(500, 'Failed to process image');
        }
    }

    private function sendSuccess(array $result): void
    {
        // Set cache control headers
        header('Cache-Control: max-age=' . $this->config->getCacheLifetime());
        header('Content-Type: image/' . $result['type']);
        header('Image-Cached: ' . ($result['cached'] ? 'cached' : 'not-cached'));

        echo $result['data'];
    }

    private function sendError(int $code, string $message): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
    }
}
